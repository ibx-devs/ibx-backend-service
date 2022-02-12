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
use App\Contracts\Repository\ISysBlockchainNetworks;
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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;


class AssetDepositController extends BaseController
{

    private $orderRepo;
    private $adsRepo;
    private $walletRepo;
    private $walletDebitLogRepo;
    private $walletCreditLogRepo;
    private $userBankAccRepo;
    private $escrowRepo;
    private $escrowReleaseRepo;
    private $sysBlockChainNetRepo;
    private $sysCurrRepo;


    public function __construct(
        IOrdersRepository $orderRepo,
        IAdsRepository $adsRepo,
        IOrderCancelledRepository $orderCancelledRepo,
        IWalletP2PRepository $walletRepo,
        IUserPaymentAccountDetail $userBankAccRepo,
        IWalletP2PDebitLogRepository $walletDebitLogRepo,
        IWalletP2PCreditLogRepository $walletCreditLogRepo,
        IEscrowRepository $escrowRepo,
        IEscrowReleaseRepository $escrowReleaseRepo,
        ISysBlockchainNetworks $sysBlockChainNetRepo,
        ISysCurrencyRepository $sysCurrRepo
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
        $this->sysBlockChainNetRepo = $sysBlockChainNetRepo;
        $this->sysCurrRepo = $sysCurrRepo;
    }

    /*************************************************************************
     * NOTE: This method assumes you have already handled network transactions
     * i.e have already connected to blockchain, generate address,send coin,
     * and get txn_id on successful. 
     ************************************************************************/

    public function networkDeposit(Request $request)
    {

        $user = $request->user('api');
        // $orderType = $request->input('order_type');

        //prepare validation rules
        $validationArray = [
            'network_type' => 'required | string',
            'address' => 'required | string',
            'txn_id' => 'required | string',
            'asset' => 'required | string',
            'qty' => 'required | int',
        ];


        /**
         * sending mail here for a test
         */

        ////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////
        try {
            Mail::raw("mail body", function ($message) {
                $message->to("nelsonsmrt@gmail.com")
                    ->subject("testing mailgun");
            });

            if (Mail::failures()) {
                Log::info("there is failure sending mail");
            } else {
                Log::info("mail sent");
            }
        } catch (\Throwable $th) {
            Log::info("there is failure sending mail" . $th);
        }


        ////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////

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

        $assetSlug =  $request->asset;
        $networkTypeSlug =  $request->network_type;

        //convert slugs to IDs
        $asset = $this->sysCurrRepo->slugToID($assetSlug);
        $networkType = $this->sysBlockChainNetRepo->slugToID($networkTypeSlug);

        //check if the slugs provided actually exist
        if (is_null($asset)) {
            $response_message = $this->customHttpResponse(401, 'Asset not found.This is really not suppose to happen here. This happens after a blockchain transaction .');
            return response()->json($response_message);
        }

        if (is_null($networkType)) {
            $response_message = $this->customHttpResponse(401, 'Network not found.This is really not suppose to happen here. This happens after a blockchain transaction . ');
            return response()->json($response_message);
        }




        $assetID = $asset->id;
        $networkTypeID = $networkType->id;
        $qty = $request->qty;

        //get all the request payload
        $reqPayload  = $request->input();

        /**
         * get order_type which will be the opposite of an Ad_type i.e
         * if Ad_type = SELL, then order_type = BUY and vice-versa.
         */

        $networkType = $reqPayload['network_type'];
        $walletAddress = $reqPayload['address'];
        $txnID = $reqPayload['txn_id'];
        $asset = $reqPayload['asset'];
        $qty = $reqPayload['qty'];


        DB::beginTransaction();
        // DB TRANSACTION for integrity purposes(NOTE: Transaction has been started above). Rollback if we encounter any error.
        try {

            //check the wallet now
            $walletInfo = $this->walletRepo->getWalletInfo($user->id, $assetID);

            //check if the wallet actually exist
            if (is_null($walletInfo)) {
                //create NEW wallet for subject asset for the user
                $newEntityID = str_replace("-", "", Str::uuid());
                $reqPayload['uuid'] = $newEntityID;
                $reqPayload['asset_id'] = $assetID;
                $reqPayload['user_id'] = $user->id;
                $reqPayload['amount'] = $qty;
                $reqPayload['last_amount_credited'] = $qty;

                $dbData  = WalletMapper::createWalletInputData($reqPayload); // a mini DTO
                $newAssetWalletID = $this->walletRepo->create($dbData);
                $walletID = $newAssetWalletID;
            } else {
                //credit already existing subject asset wallet.
                $this->walletRepo->creditWallet($walletInfo->uuid, $qty);
                $walletID =  $walletInfo->id;
            }


            //2. log credit in wallet credit log
            $newEntityID = str_replace("-", "", Str::uuid());

            $reqPayload['uuid'] = $newEntityID;
            $reqPayload['user_id'] = $user->id;
            $reqPayload['wallet_id'] = $walletID;
            $reqPayload['amount'] = $qty;
            $reqPayload['network_type_id'] = $networkTypeID;
            $reqPayload['address'] = $walletAddress;
            $reqPayload['txn_id'] = $txnID;
            $reqPayload['cashflow_channel_id'] = 1; // where channel 1 = "NETWORK" 

            $dbData  = WalletCreditMapper::createWalletCreditInputData($reqPayload); // a mini DTO
            $this->walletCreditLogRepo->create($dbData);




            DB::commit();
            $message = "Account successfully funded.";
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
}
