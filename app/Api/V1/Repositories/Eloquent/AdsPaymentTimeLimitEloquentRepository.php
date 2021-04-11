<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\AdsPaymentTimeLimit;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IAdsPaymentTimeLimitRepository;

class AdsPaymentTimeLimitEloquentRepository extends  EloquentRepository implements IAdsPaymentTimeLimitRepository
{

    public $adsPTL;
    public function __construct(AdsPaymentTimeLimit $adsPTL)
    {
        parent::__construct();
        $this->adsPTL =  $adsPTL;
    }


    public function model()
    {
        return AdsPaymentTimeLimit::class;
    }

    public function slugToID($slug)
    {
        $res = $this->adsPTL->select('id')
            ->where('slug', '=', $slug)
            ->first();
        return $res;
    }
}
