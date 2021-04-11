<?php


namespace App\Api\V1\Controllers;

use App\Contracts\Repository\IAdsConditionTypesRepository;
use App\Contracts\Repository\IAdsCounterpartyConditionsRepository;
use App\Contracts\Repository\IAdsPaymentMethodRepository;
use App\Contracts\Repository\IAdsPaymentTimeLimitRepository;
use App\Contracts\Repository\IAdsRepository;
use App\Contracts\Repository\IPaymentMethodsRepository;
use App\Contracts\Repository\ISysCurrencyRepository;
use App\Contracts\Repository\IUserPaymentAccountDetail;
use App\Utils\AdsCPCMapper;
use App\Utils\AdsMapper;
use App\Utils\AdsPMMapper;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;


class AdsController extends BaseController
{

    private $adsRepo;
    private $adsPayTimeLimitRepo;
    private $adsCPCRepo;
    private $adsCondTypeRepo;
    private $PaymentMethodRepo;
    private $adsPM;
    private $sysCurrRepo;
    private $userPaymentAccDetailRepo;

    public function __construct(
        IAdsRepository $adsRepo,
        IAdsPaymentTimeLimitRepository $adsPayTimeLimitRepo,
        IAdsCounterpartyConditionsRepository $adsCPCRepo,
        IAdsConditionTypesRepository $adsCondTypeRepo,
        IPaymentMethodsRepository $PaymentMethodRepo,
        IAdsPaymentMethodRepository $adsPM,
        ISysCurrencyRepository $sysCurrRepo,
        IUserPaymentAccountDetail $userPaymentAccDetailRepo
    ) {
        $this->adsRepo = $adsRepo;
        $this->adsPayTimeLimitRepo = $adsPayTimeLimitRepo;
        $this->adsCPCRepo = $adsCPCRepo;
        $this->adsCondTypeRepo = $adsCondTypeRepo;
        $this->PaymentMethodRepo = $PaymentMethodRepo;
        $this->adsPM = $adsPM;
        $this->sysCurrRepo = $sysCurrRepo;
        $this->userPaymentAccDetailRepo = $userPaymentAccDetailRepo;
    }

    public function findAll()
    {
        $result = $this->adsRepo->findAllDetailed();
        $prunedResult = AdsMapper::pruneDetailed($result);
        $populatedResult = [];
        foreach ($prunedResult as $entity) {
            $adsID = $entity->uuid;
            $adsPMRes = $this->adsPM->filterByAdsId($adsID);
            $adsCPCRes = $this->adsCPCRepo->filterByAdsId($adsID);
            $entity['payment_methods'] = $adsPMRes;
            $entity['counterparty_conditions'] = $adsCPCRes;
            $populatedResult[] = $entity;
        }
        $response_message = $this->customHttpResponse(200, 'Success.', $populatedResult);
        return response()->json($response_message);
    }

    public function findOne($id)
    {
        $result = $this->adsRepo->findOneDetailed($id);
        $prunedResult = AdsMapper::pruneDetailed($result);
        $adsID = $prunedResult->uuid;
        $adsPMRes = $this->adsPM->filterByAdsId($adsID);
        $adsCPCRes = $this->adsCPCRepo->filterByAdsId($adsID);
        $prunedResult['payment_methods'] = $adsPMRes;
        $prunedResult['counterparty_conditions'] = $adsCPCRes;
        $response_message = $this->customHttpResponse(200, 'Success.', $prunedResult);
        return response()->json($response_message);
    }

    public function pageData()
    {
        $res1 = $this->adsPayTimeLimitRepo->findAll();
        $res3 = $this->adsCondTypeRepo->findAll();
        $res4 = $this->PaymentMethodRepo->findAll();
        $res5 = $this->sysCurrRepo->getP2PLocal();
        $res6 = $this->sysCurrRepo->getP2PForeign();

        $result = [
            'payment_time_limit' => AdsMapper::prune($res1),
            'payment_methods' => AdsMapper::prune($res4),
            'asset_local' => $res5,
            'asset_foreign' => $res6,
        ];
        $response_message = $this->customHttpResponse(200, 'Success.', $result);
        return response()->json($response_message);
    }


    public function create(Request $request)
    {
        $user = $request->user('api');
        $adType = $request->input('ad_type');

        //prepare validation rules
        $validationArray = [
            'ad_type' => 'required | in:buy,sell',
            'qty' => 'required | int',
            'price' => 'required | int',
            'min_order' => 'required | int',
            'max_order' => 'required | int',
            'asset' => 'required | string',
            'with_fiat' => 'required | string',
            'payment_timeout' => 'required | string',
            'payment_methods' => 'required | array | max:2',
        ];
        $adType == "buy" ?
            $validationArray['payment_methods.*.type'] = 'required | string' :
            $validationArray['payment_methods.*.user_acc_detail_id'] = 'required | string';

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

        //get all the request payload
        $reqPayload  = $request->input();

        //get some of the payload parameters that needs conversion
        $assetSlug =  $reqPayload['asset'];
        $withFiatSlug =  $reqPayload['with_fiat'];
        $paymentTimeoutSlug =  $reqPayload['payment_timeout'];

        //convert slugs to IDs
        $asset = $this->sysCurrRepo->slugToID($assetSlug);
        $fiat = $this->sysCurrRepo->slugToID($withFiatSlug);
        $paymentTimeout = $this->adsPayTimeLimitRepo->slugToID($paymentTimeoutSlug);

        //check if the slugs provided actually exist
        if (is_null($asset)) {
            $response_message = $this->customHttpResponse(401, 'Asset not found.');
            return response()->json($response_message);
        }

        if (is_null($fiat)) {
            $response_message = $this->customHttpResponse(401, 'Fiat not found.');
            return response()->json($response_message);
        }

        if (is_null($paymentTimeout)) {
            $response_message = $this->customHttpResponse(401, 'Payment timeout not found.');
            return response()->json($response_message);
        }

        //append some extra computed parameters to the request payload
        $newEntityID = Str::uuid();
        $reqPayload['user_id'] = $user->id;
        $reqPayload['uuid'] = $newEntityID;
        $reqPayload['asset'] = $asset->id;
        $reqPayload['with_fiat'] = $fiat->id;
        $reqPayload['payment_timeout'] = $paymentTimeout->id;

        // with the data we have so far, lets create the new entity(ies) but within a 
        // DB TRANSACTION for integrity purposes. Rollback if we encounter any error.
        try {
            DB::beginTransaction();

            //create ad
            $adData  = AdsMapper::createAdsInputData($reqPayload); // a mini DTO
            $newAdID = $this->adsRepo->createAd($adData);

            //append more data for Counter party condition creation
            $newEntityID = str_replace("-", "", Str::uuid());
            $reqPayload['cpc_uuid'] = $newEntityID;
            $reqPayload['ads_id'] = $newAdID;

            $adData  = AdsCPCMapper::createAdsCPCInputData($reqPayload); // a mini DTO
            $res1 = $this->adsCPCRepo->createAdCPCondition($adData);


            //insert all the payment methods
            foreach ($reqPayload['payment_methods'] as $pm) {
                //append more data for payment method creation
                $newEntityID = str_replace("-", "", Str::uuid());
                $reqPayload['ad_pm_uuid'] = $newEntityID;
                $reqPayload['ads_id'] = $newAdID;

                if ($reqPayload['ad_type'] === "buy") {

                    $PMID = $this->PaymentMethodRepo->slugToID($pm['type']);
                    if (is_null($PMID)) {
                        $response_message = $this->customHttpResponse(401, 'Payment method not found.');
                        return response()->json($response_message);
                    }

                    //append buy data
                    $reqPayload['pm_id'] = $PMID->id;

                    $adPMBuyData  = AdsPMMapper::createAdPMBuyInputData($reqPayload); // a mini DTO
                    $res1 = $this->adsPM->createAdPMBuy($adPMBuyData);
                } else {
                    $userAccDetail = $this->userPaymentAccDetailRepo->getIDsByUUID($pm['user_acc_detail_id']);
                    if (is_null($userAccDetail)) {
                        $response_message = $this->customHttpResponse(401, 'Payment method not found.');
                        return response()->json($response_message);
                    }

                    // append sell data. Note: SELL ads need the crypto seller's bank detail so buyers can send fiat
                    // Hence , the need for the extra data here, "pm_acc_detail_id"
                    $reqPayload['pm_id'] = $userAccDetail->payment_method_id;
                    $reqPayload['pm_acc_detail_id'] = $userAccDetail->id;

                    $adPMSellData  = AdsPMMapper::createAdPMSellInputData($reqPayload); // a mini DTO
                    $res1 = $this->adsPM->createAdPMSell($adPMSellData);
                }
            }
            DB::commit();
            $response_message = $this->customHttpResponse(200, 'Added successfully.');
            return response()->json($response_message);
        } catch (\Throwable $th) {
            DB::rollBack();
            $errMsg = $th->getMessage();
            Log::info("Create ads error ===> " . $th);
            $response_message = $this->customHttpResponse(500, 'Some error occured.', $errMsg);
            return response()->json($response_message);
        }
    }
}
