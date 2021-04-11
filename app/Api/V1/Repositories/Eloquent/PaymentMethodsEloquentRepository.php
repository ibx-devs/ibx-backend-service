<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\PaymentMethods;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IPaymentMethodsRepository;

class PaymentMethodsEloquentRepository extends  EloquentRepository implements IPaymentMethodsRepository
{

    private $PM;

    public function __construct(PaymentMethods $PM)
    {
        parent::__construct();
        $this->PM = $PM;
    }


    public function model()
    {
        return PaymentMethods::class;
    }

    public function slugToID($slug)
    {
        $res = $this->PM->select('id')
            ->where('slug', '=', $slug)
            ->first();
        return $res;
    }
}
