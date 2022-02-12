<?php


namespace App\Api\V1\Controllers;

use App\Contracts\Repository\IAdsConditionTypesRepository;
use App\Contracts\Repository\IAdsCounterpartyConditionsRepository;
use App\Contracts\Repository\IAdsPaymentMethodRepository;
use App\Contracts\Repository\IAdsPaymentTimeLimitRepository;
use App\Contracts\Repository\IAdsRepository;
use App\Contracts\Repository\IAdsVisit;
use App\Contracts\Repository\IPaymentMethodsRepository;
use App\Contracts\Repository\ISysCurrencyRepository;
use App\Contracts\Repository\IUserPaymentAccountDetail;
use App\Utils\AdsCPCMapper;
use App\Utils\AdsMapper;
use App\Utils\AdsPMMapper;
use App\Utils\AdsVisitMapper;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;


class AdsVisitController extends BaseController
{

    private $adsRepo;
    private $adsVisitRepo;

    public function __construct(
        IAdsRepository $adsRepo,
        IAdsVisit $adsVisitRepo
    ) {
        $this->adsRepo = $adsRepo;
        $this->adsVisitRepo = $adsVisitRepo;
    }


    public function findAll()
    {
        $result = $this->adsVisitRepo->findAllDetailed();
        $prunedResult = AdsVisitMapper::pruneDetailed($result);
        $response_message = $this->customHttpResponse(200, 'Success.', $prunedResult);
        return response()->json($response_message);
    }

    public function findOne($id)
    {
        $result = $this->adsVisitRepo->findOneDetailed($id);

        if (is_null($result)) {
            $response_message = $this->customHttpResponse(404, 'No visit with that id was recorded.');
            return response()->json($response_message, 404);
        }

        $prunedResult = AdsVisitMapper::pruneDetailed($result);
        $response_message = $this->customHttpResponse(200, 'Success.', $prunedResult);
        return response()->json($response_message);
    }


    public function create(Request $request, $id)
    {
        $user = $request->user('api');

        //convert slugs to IDs
        $adInfo = $this->adsRepo->getAdInfo($id);

        //check if the ad id provided actually exist
        if (is_null($adInfo)) {
            $response_message = $this->customHttpResponse(401, 'Ads not found.Check the ad id.');
            return response()->json($response_message);
        }

        //check if the creator of ad wants to place order against himself(NOT ALLOWED).
        if ($adInfo->creator === $user->id) {
            $response_message = $this->customHttpResponse(401, 'You cannot place an order against your own ad.');
            return response()->json($response_message);
        }



        //append some extra computed parameters to the request payload
        $newEntityID = str_replace("-", "", Str::uuid());
        $reqPayload['user_id'] = $user->id;
        $reqPayload['uuid'] = $newEntityID;
        $reqPayload['ad_id'] = $adInfo->id;


        // with the data we have so far, lets create the new entity(ies) but within a 
        // DB TRANSACTION for integrity purposes. Rollback if we encounter any error.
        try {
            DB::beginTransaction();

            //create ad
            $adData  = AdsVisitMapper::createAdsVisitInputData($reqPayload); // a mini DTO
            $newAdID = $this->adsVisitRepo->createAdVisit($adData);

            $result = $this->adsRepo->incrementVisit($id);

            DB::commit();
            $response_message = $this->customHttpResponse(200, 'Visit recorded successfully.');
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
