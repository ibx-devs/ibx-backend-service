<?php


namespace App\Api\V1\Controllers;

use App\Api\V1\Models\EscrowRelease;
use App\Contracts\Repository\IAdsConditionTypesRepository;
use App\Contracts\Repository\IAdsCounterpartyConditionsRepository;
use App\Contracts\Repository\IAdsPaymentMethodRepository;
use App\Contracts\Repository\IAdsPaymentTimeLimitRepository;
use App\Contracts\Repository\IAdsRepository;
use App\Contracts\Repository\IEscrowReleaseRepository;
use App\Contracts\Repository\IEscrowRepository;
use App\Contracts\Repository\IOrderCancelledRepository;
use App\Contracts\Repository\IOrdersRepository;
use App\Contracts\Repository\IPaymentMethodsRepository;
use App\Contracts\Repository\ISysCurrencyRepository;
use App\Contracts\Repository\IUserPaymentAccountDetail;
use App\Contracts\Repository\IWalletP2PCreditLogRepository;
use App\Contracts\Repository\IWalletP2PDebitLogRepository;
use App\Contracts\Repository\IWalletP2PRepository;
use App\Utils\AdsCPCMapper;
use App\Utils\AdsMapper;
use App\Utils\AdsPMMapper;
use App\Utils\EscrowMapper;
use App\Utils\EscrowReleaseMapper;
use App\Utils\OrderCancelledMapper;
use App\Utils\OrderMapper;
use App\Utils\WalletCreditMapper;
use App\Utils\WalletDebitMapper;
use App\Utils\WalletMapper;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;


class OrderController extends BaseController
{

    private $orderRepo;
    private $adsRepo;
    private $walletRepo;
    private $walletDebitLogRepo;
    private $walletCreditLogRepo;
    private $userBankAccRepo;
    private $escrowRepo;
    private $escrowReleaseRepo;


    public function __construct(
        IOrdersRepository $orderRepo,
        IAdsRepository $adsRepo,
        IOrderCancelledRepository $orderCancelledRepo,
        IWalletP2PRepository $walletRepo,
        IUserPaymentAccountDetail $userBankAccRepo,
        IWalletP2PDebitLogRepository $walletDebitLogRepo,
        IWalletP2PCreditLogRepository $walletCreditLogRepo,
        IEscrowRepository $escrowRepo,
        IEscrowReleaseRepository $escrowReleaseRepo
    ) {
        $this->orderRepo = $orderRepo;
        $this->adsRepo = $adsRepo;
        $this->orderCancelledRepo = $orderCancelledRepo;
        $this->walletRepo = $walletRepo;
        $this->walletDebitLogRepo = $walletDebitLogRepo;
        $this->walletCreditLogRepo = $walletCreditLogRepo;
        $this->userBankAccRepo = $userBankAccRepo;
        $this->escrowRepo = $escrowRepo;
        $this->escrowReleaseRepo = $escrowReleaseRepo;
    }

    public function findAll()
    {
        $result = $this->orderRepo->findAllDetailed();
        $prunedResult = OrderMapper::pruneDetailed($result);
        $response_message = $this->customHttpResponse(200, 'Success.', $prunedResult);
        return response()->json($response_message);
    }

    public function findOne($id)
    {
        $result = $this->orderRepo->findOneDetailed($id);
        $prunedResult = OrderMapper::pruneDetailed($result);
        $response_message = $this->customHttpResponse(200, 'Success.', $prunedResult);
        return response()->json($response_message);
    }




    public function create(Request $request)
    {
        $user = $request->user('api');
        $orderType = $request->input('order_type');

        //prepare validation rules
        $validationArray = [
            'ad_id' => 'required | string',
            'order_type' => 'required | in:buy,sell',
            'amount' => 'required | int',
            'qty' => 'required | int',
        ];

        $orderType == "sell" ? $validationArray['payment_acc_id'] = 'required | string' : null;

        //apply validation rule array above to a custom validator    
        $validator = Validator::make(
            $request->input(),
            $validationArray
        );

        //check if validation fails and halt execution with nice message
        if ($validator->fails()) {

            $errors = $validator->errors();

            $response_message = $this->customHttpResponse(401, 'Incorrect details.', $errors);
            return response()->json($response_message);
        }

        if ($orderType === "sell") {
            //check if the payment_acc_id supplied actually exist
            $bankAccID = $request->input('payment_acc_id');
            $accInfo = $this->userBankAccRepo->getIDsByUUID($bankAccID, $user->id);


            if (is_null($accInfo)) {
                $response_message = $this->customHttpResponse(404, "Payment method is invalid or not supported in this ad. Select payment method that match buyer's options.");
                return response()->json($response_message);
            }

            $reqPayload['payment_acc_internal_id'] = $accInfo->id;
        }
        //get all the request payload
        $reqPayload  = $request->input();

        /**
         * get order_type which will be the opposite of an Ad_type i.e
         * if Ad_type = SELL, then order_type = BUY and vice-versa.
         */
        $adID = $reqPayload['ad_id'];
        $amount = $reqPayload['amount'];
        $qty = $reqPayload['qty'];


        /**
         * starting this transaction here because the next statement "getAdInfo($adID)" below has a pessimistic locking.
         * i.e prevent concurrent access (race condition) to the ad info to avoid future double spending
         * or payment
         */
        DB::beginTransaction();

        $adInfo = $this->adsRepo->getAdInfo($adID);

        // auto-detect order type i.e opposite of ad type
        // $orderType = $adInfo->ad_type == "sell" ? "buy" : "sell";

        if ($orderType === "sell") {
            //check if subject asset is sufficient
            $assetID = $adInfo->asset_id;

            //check the wallet now
            $walletInfo = $this->walletRepo->getWalletInfo($user->id, $assetID);

            //check if the wallet actually exist
            if (is_null($walletInfo)) {
                $response_message = $this->customHttpResponse(404, 'your account for the provided asset is empty.');
                return response()->json($response_message);
            }

            //check if the amount is empty
            if (is_null($walletInfo->amount) ||  $walletInfo->amount <= 0) {
                $response_message = $this->customHttpResponse(404, 'your account for the provided asset is empty.');
                return response()->json($response_message);
            }

            //check if the seller has enough coin 
            if ($walletInfo->amount < $qty) {

                $response_message = $this->customHttpResponse(404, 'You dont have up to that amount. Reduce the order and try again.');
                return response()->json($response_message);
            }
        }

        //check if the ad_id supplied actually exist
        if (is_null($adInfo)) {
            $response_message = $this->customHttpResponse(404, 'Ad with the provided id not found.', $adInfo);
            return response()->json($response_message);
        }


        //check if order qty of coin is within range.

        if ($qty <= 0) {
            $response_message = $this->customHttpResponse(401, 'Qty of coin is required.');
            return response()->json($response_message);
        }

        if ($qty > $adInfo->operating_qty) {
            $response_message = $this->customHttpResponse(401, 'Qty of coin is too high. Looks like available quantity has changed due to high demand. Adjust the qty or go back to trade and chose ads with higher qty.');
            return response()->json($response_message);
        }

        //check if order amount is within range.

        if ($amount < $adInfo->min_order) {
            $response_message = $this->customHttpResponse(401, 'Amount is too low. Adjust the amount or go back to trade and chose ads with lower amount.');
            return response()->json($response_message);
        }

        if ($amount > $adInfo->max_order) {
            $response_message = $this->customHttpResponse(401, 'Amount is too high. Adjust the amount or go back to trade and chose ads with higher amount.');
            return response()->json($response_message);
        }

        //check if the creator of ad wants to place order against himself(NOT ALLOWED).
        if ($adInfo->creator === $user->id) {
            $response_message = $this->customHttpResponse(401, 'You cannot place an order against your own ad.');
            return response()->json($response_message);
        }

        //ad type and order type is directly OPPOSITE and CANNOT be the same.
        if ($adInfo->ad_type  === $orderType) {
            $response_message = $this->customHttpResponse(401, 'Request doesnt make sense. Ad type and order type cannot be the same. A seller cannot sell to a seller of the same asset in a transaction and vice versa.');
            return response()->json($response_message);
        }



        //append some extra computed parameters to the request payload
        $newEntityID = str_replace("-", "", Str::uuid());
        $reqPayload['user_id'] = $user->id;
        $reqPayload['uuid'] = $newEntityID;
        $reqPayload['order_type'] = $orderType;
        $reqPayload['ad_internal_id'] = $adInfo->id;




        // with the data we have so far, lets create the new entity(ies) but within a 
        // DB TRANSACTION for integrity purposes(NOTE: Transaction has been started above). Rollback if we encounter any error.
        try {

            //deduct/debit the Ad.
            $this->adsRepo->debitAd($adID, $qty);

            //create order
            $adData  = OrderMapper::createOrderInputData($reqPayload); // a mini DTO
            $newOrderID = $this->orderRepo->createOrder($adData);

            //if this is a Sell order, then debit wallet and send to ESCROW
            if ($orderType === "sell") {
                $this->walletRepo->debitWallet($walletInfo->uuid, $qty);

                $newEntityID = str_replace("-", "", Str::uuid());
                $reqPayload['uuid'] = $newEntityID;
                $reqPayload['wallet_type'] = 1;   //references sys_wallet_types table. This is a p2p service hence "1";
                $reqPayload['wallet_id'] = $walletInfo->id;
                $reqPayload['order_id'] = $newOrderID;
                /**
                 * Escrow type tells the type of SELL operation this is. There are two types of sell in the 
                 * system , either a sell ORDER or a sell AD. At the ad service, this would be "sell_ad"
                 */
                $reqPayload['escrow_type'] = "sell_order";

                $dbData  = EscrowMapper::createEscrowInputData($reqPayload); // a mini DTO
                $newEscrowID = $this->escrowRepo->create($dbData);


                //append some extra computed parameters to the request payload
                $newEntityID = str_replace("-", "", Str::uuid());
                $reqPayload['uuid'] = $newEntityID;
                $reqPayload['escrow_id'] = $newEscrowID;

                /**
                 * This whole service/script is about TRADE, and making reference to the "sys_wallet_cashflow_channel"
                 * DB, TRADE has id = 4. In some other services e.g services that handle TRANSFERS, or
                 * Network/Blockchain access,etc this channel_id will be different according to what is on the DB.
                 */
                $reqPayload['cashflow_channel_id'] = 4; // where channel 4 = "TRADE" 

                $dbData  = WalletDebitMapper::createWalletDebitInputData($reqPayload); // a mini DTO
                $this->walletDebitLogRepo->create($dbData);
            }

            //TODO : Notify the buyer(sell type) or seller (buy type) by email or sms and live update(firebase) that a new order has just been placed so they take the next action.



            DB::commit();
            $message = $orderType === "sell" ? "Order created successfully. Now wait for the Buyer to make payment. PLEASE CONFIRM PAYMENT before releasing coins." :
                "Order created successfully. Now make payment to the seller and hit the [TRANSFERED] button or equivalent to notify the seller so they can release your coin.";
            $response_message = $this->customHttpResponse(200, $message);
            return response()->json($response_message);
        } catch (\Throwable $th) {
            DB::rollBack();
            $errMsg = $th->getMessage();
            Log::info("Create order error ===> " . $th);
            $response_message = $this->customHttpResponse(500, 'Some error occured.', $errMsg);
            return response()->json($response_message);
        }
    }


    public function buyerConfirm(Request $request, $orderID)
    {

        $user = $request->user('api');
        //check if the order id provided actually exist
        $orderInfo = $this->orderRepo->findByUUID($orderID);

        if (is_null($orderInfo)) {
            $response_message = $this->customHttpResponse(401, 'order with the id not found.');
            return response()->json($response_message);
        }

        //check if buyer had already confirmed transfer of cash payment to seller
        if (!is_null($orderInfo->buyer_transfered_at)) {
            $response_message = $this->customHttpResponse(401, 'You have already confirmed transfer of payment. Kindly excercise some patience while the seller confirms and release your coins.');
            return response()->json($response_message);
        }

        //check to make sure its the right user that wants to perform THIS action(Confirm Fund transfer)
        if ($orderInfo->order_type === "sell") {
            //for a SELL order, the buyer is the "AD" creator and that will be the person allowed to perform THIS action
            if ($user->id !== $orderInfo->ad_creator) {
                $response_message = $this->customHttpResponse(401, 'only the buyer(ad creator) is allowed to perform this action');
                return response()->json($response_message);
            }
        } else {
            //for a BUY order, the buyer is the "ORDER" creator and that will be the person allowed to perform THIS action
            if ($user->id !== $orderInfo->order_creator) {
                $response_message = $this->customHttpResponse(401, 'only the buyer(order creator) is allowed to perform this action');
                return response()->json($response_message);
            }
        }



        try {
            $result = $this->orderRepo->buyerConfirmTransfer($orderID);


            /**
             * TODO: 
             *  - notify(sms and Email) seller to confirm payment from buyer and release coin
             *  - send live update to seller too (firebase)
             */


            $response_message = $this->customHttpResponse(200, 'Success.');
            return response()->json($response_message);
        } catch (\Throwable $th) {
            //throw $th;
            Log::info("buyer confirm error ==" . $th);
            $response_message = $this->customHttpResponse(500, 'DB Error.');
            return response()->json($response_message);
        }
    }

    public function sellerConfirm(Request $request, $orderID)
    {

        $user = $request->user('api');

        //check if the order id provided actually exist
        $orderInfo = $this->orderRepo->findByUUID($orderID);

        if (is_null($orderInfo)) {
            $response_message = $this->customHttpResponse(401, 'order with the id not found.');
            return response()->json($response_message);
        }

        //check if seller had already confirmed receipt of this order
        if (!is_null($orderInfo->seller_confirmed_at)) {
            $response_message = $this->customHttpResponse(401, 'You have already confirmed receipt of this transaction. Therefore , you have successfully sold your coins and that marks the end of this transaction.');
            return response()->json($response_message);
        }

        //check to make sure its the right user that wants to perform THIS action(Confirm Fund transfer)
        if ($orderInfo->order_type === "sell") {
            //for a SELL order, the seller is the "ORDER" creator and that will be the person allowed to perform THIS action
            if ($user->id !== $orderInfo->order_creator) {
                $response_message = $this->customHttpResponse(401, 'only the seller(order creator) is allowed to perform this action');
                return response()->json($response_message);
            }
            $buyerID = $orderInfo->ad_creator;
        } else {
            //for a BUY order, the seller is the "AD" creator and that will be the person allowed to perform THIS action
            if ($user->id !== $orderInfo->ad_creator) {
                $response_message = $this->customHttpResponse(401, 'only the seller(ad creator) is allowed to perform this action');
                return response()->json($response_message);
            }
            $buyerID = $orderInfo->order_creator;
        }


        DB::beginTransaction(); //started transaction here cos the method "findByOrderId()" below is lockedForUpdate.


        $escrowInfo = $this->escrowRepo->findByOrderId($orderInfo->id, $user->id);

        //check if escrow was not created for this sell order(THIS SHOULD NEVER HAPPEN).
        if (is_null($escrowInfo)) {
            $response_message = $this->customHttpResponse(401, 'Oops! Coin custody did not complete. Kindly refund the buyer if he/she had already completed transfer. We are looking into this.');
            return response()->json($response_message);
        }

        //check if escrow balance for this sell order is less than the pending order's amount(THIS SHOULD NEVER HAPPEN).
        if ($escrowInfo->balance < $orderInfo->qty) {
            $response_message = $this->customHttpResponse(401, 'Oops! You reached here i.e there is still pending transaction but the custody balance for this order is lesser.Kindly refund the buyer if he/she has already completed transfer. We are looking into this.');
            return response()->json($response_message);
        }




        /** 
         * Hey wait! one more thing before we proceed. A seller should not be allowed to confirm
         * receipt of payment if the buyer has not yet confirmed transfer of funds from his/her end(buyer's end).
         * In short the frontend(View - web or mobile) should disable the seller's "RELEASE COIN" button (or the equivalent)
         * if the buyer has not hit the "I HAVE TRANSFERED" button (or equivalent) from his/her end (buyer's end).
         * 
         * NOTE: even at that, a seller SHOULD NOT release coin if he/she has not gotten actual bank alert from the buyer.
         * 
         */
        if (is_null($orderInfo->buyer_transfered_at)) {
            $response_message = $this->customHttpResponse(401, 'Please wait for the buyer to transfer your funds first before releasing coin.');
            return response()->json($response_message);
        }


        try {
            $result = $this->orderRepo->sellerConfirmReceipt($orderID);

            /**
             * TODO: 
             *  - move value from ESCROW to the buyers wallet 
             *  - notify(sms and Email) buyer
             *  - send live update to buyer(firebase)
             */
            $orderAmount = $orderInfo->qty;
            $escrowID = $escrowInfo->uuid;
            // $buyerID = $orderInfo->ad_creator;

            //check if buyer has a wallet for the subject asset(coin)
            $assetID = $orderInfo->asset_id;
            $walletInfo = $this->walletRepo->getWalletInfo($buyerID, $assetID);

            //1. credit Buyer wallet
            //check if buyer has a wallet for the given asset, create one with details here. Update if otherwise.
            if (is_null($walletInfo)) {
                //create wallet for the subject asset for buyer and credit it .
                $newEntityID = str_replace("-", "", Str::uuid());
                $reqPayload['uuid'] = $newEntityID;
                $reqPayload['asset_id'] = $assetID;
                $reqPayload['user_id'] = $buyerID;
                $reqPayload['amount'] = $orderAmount;
                $reqPayload['last_amount_credited'] = $orderAmount;

                $dbData  = WalletMapper::createWalletInputData($reqPayload); // a mini DTO
                $newAssetWalletID = $this->walletRepo->create($dbData);
                $buyerAssetWalletID = $newAssetWalletID;
            } else {
                //credit buyer's already existing subject asset wallet.
                $this->walletRepo->creditWallet($walletInfo->uuid, $orderAmount);
                $buyerAssetWalletID =  $walletInfo->id;
            }

            //2. log credit in wallet credit log
            $newEntityID = str_replace("-", "", Str::uuid());

            $reqPayload['uuid'] = $newEntityID;
            $reqPayload['user_id'] = $buyerID;
            $reqPayload['wallet_id'] = $buyerAssetWalletID;
            $reqPayload['order_id'] = $orderInfo->id;
            $reqPayload['amount'] = $orderAmount;
            $reqPayload['escrow_id'] = $escrowInfo->id;
            $reqPayload['cashflow_channel_id'] = 4; // where channel 4 = "TRADE" 

            $dbData  = WalletCreditMapper::createWalletCreditInputData($reqPayload); // a mini DTO
            $this->walletCreditLogRepo->create($dbData);


            //3. Release money from escrow
            $this->escrowRepo->releaseCoinByOrderId($orderAmount, $orderInfo->id, $user->id);

            //4. Log escrow release
            $newEntityID = str_replace("-", "", Str::uuid());
            $reqPayload['uuid'] = $newEntityID;
            $reqPayload['escrow_id'] = $escrowInfo->id;
            $reqPayload['amount'] = $orderAmount;
            $reqPayload['balance'] = ($escrowInfo->amount - $orderAmount);
            $reqPayload['wallet_type'] = 1; // referencing the sys_wallet_type table in db. Where 1 = p2p
            $reqPayload['wallet_id'] = $buyerAssetWalletID;


            $dbData  = EscrowReleaseMapper::createEscrowReleaseInputData($reqPayload); // a mini DTO
            $this->escrowReleaseRepo->create($dbData);

            //TODO: Notify the buyer (via SMS or email or both ) that his/her coins have arrived.
            //       and send live update to buyer(firebase).



            DB::commit();
            $response_message = $this->customHttpResponse(200, 'Success.');
            return response()->json($response_message);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            Log::info("seller confirm error ==" . $th);
            $response_message = $this->customHttpResponse(500, 'DB Error.');
            return response()->json($response_message);
        }
    }

    public function cancelOrder(Request $request, $orderID)
    {

        $user = $request->user('api');
        $reqPayload = $request->input();
        //check if the order id provided actually exist
        $res = $this->orderRepo->findByUUID($orderID);




        if (is_null($res)) {
            $response_message = $this->customHttpResponse(401, 'order with the id not found.');
            return response()->json($response_message);
        }

        //prevent users from canceling orders they didnt create.
        if ($res->order_type === 'sell') {
            $response_message = $this->customHttpResponse(401, 'Sell ads cannot be canceled. Please wait for the buyer to make a transfer and then realease the coins.');
            return response()->json($response_message);
        }

        //prevent users from canceling orders they didnt create.
        if ($res->order_creator !== $user->id) {
            $response_message = $this->customHttpResponse(401, 'you can only cancel BUY orders you created.');
            return response()->json($response_message);
        }

        $adID  = $res->ad_uuid;
        $orderQty = $res->qty;

        //check if order was already cancelled
        if (!is_null($res->cancelled_at)) {
            $response_message = $this->customHttpResponse(401, 'Order already cancelled.');
            return response()->json($response_message);
        }

        //prepare validation rules
        $validationArray = [
            'reason' => 'sometimes | required | string',
        ];

        //apply validation rule array above to a custom validator    
        $validator = Validator::make(
            $request->input(),
            $validationArray
        );

        //check if validation fails and halt execution with nice message
        if ($validator->fails()) {

            $errors = $validator->errors();

            $response_message = $this->customHttpResponse(401, 'Incorrect details.', $errors);
            return response()->json($response_message);
        }

        $reason = $request->input('reason');
        $reason = $request->has('reason') ? $reason : null;


        try {
            DB::beginTransaction();
            $result = $this->orderRepo->cancelOrder($orderID);

            //refund the Ad back.
            $this->adsRepo->creditAd($adID, $orderQty);

            $orderIDInternal = $res->id;

            $newEntityID = str_replace("-", "", Str::uuid());
            $reqPayload['user_id'] = $user->id;
            $reqPayload['uuid'] = $newEntityID;
            $reqPayload['order_id'] = $orderIDInternal;
            $reqPayload['reason'] = $reason;


            //log canceled order with reason
            $cancelData  = OrderCancelledMapper::createOrderCancelInputData($reqPayload); // a mini DTO
            $result = $this->orderCancelledRepo->create($cancelData);

            //refund the order quantity back to the parent ad.

            DB::commit();

            $response_message = $this->customHttpResponse(200, 'Success.', $result);
            return response()->json($response_message);
        } catch (\Throwable $th) {
            DB::rollBack();
            //throw $th;
            Log::info("visit error ==" . $th);
            $response_message = $this->customHttpResponse(500, 'DB Error.');
            return response()->json($response_message);
        }
    }
}
