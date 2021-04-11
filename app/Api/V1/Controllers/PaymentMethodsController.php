<?php


namespace App\Api\V1\Controllers;

use App\Contracts\Repository\IPaymentMethodsRepository;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class PaymentMethodsController extends BaseController
{

    private $PMRepo;

    public function __construct(IPaymentMethodsRepository $PMRepo)
    {
        $this->PMRepo = $PMRepo;
    }

    public function findAll()
    {
        $result = $this->PMRepo->findAll();
        $response_message = $this->customHttpResponse(200, 'Success.', $result);
        return response()->json($response_message);
    }

    public function findOne($id)
    {
        $result = $this->PMRepo->find($id);
        $response_message = $this->customHttpResponse(200, 'Success.', $result);
        return response()->json($response_message);
    }
}
