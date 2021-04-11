<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\AdsPaymentMethod;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IAdsPaymentMethodRepository;

class AdsPaymentMethodEloquentRepository extends  EloquentRepository implements IAdsPaymentMethodRepository
{

    private $adsPayment;

    public function __construct(AdsPaymentMethod $adsPayment)
    {
        parent::__construct();
        $this->adsPayment = $adsPayment;
    }

    public function model()
    {
        return AdsPaymentMethod::class;
    }

    public function findAllDetailed()
    {
        $res = $this->adsPayment->from('ads_payment_method as a')
            ->select(
                'a.uuid',
                'ads.uuid as ads_id',
                'pm.uuid as pm_id',
                'pm.name as pm_name',
                'pm.slug as pm_slug',
                'pm.descr as pm_descr',
                'a.is_active',
            )
            ->leftJoin('payment_methods as pm', 'a.payment_method_id', 'pm.id')
            ->leftJoin('ads as ads', 'a.ads_id', 'ads.id')
            ->whereNull('a.deleted_at')
            ->get();

        return $res;
    }

    public function findOneDetailed($id)
    {
        $res = $this->adsPayment->from('ads_payment_method as a')
            ->select(
                'a.uuid',
                'ads.uuid as ads_id',
                'pm.uuid as pm_id',
                'pm.name as pm_name',
                'pm.slug as pm_slug',
                'pm.descr as pm_descr',
                'a.is_active',
            )
            ->leftJoin('payment_methods as pm', 'a.payment_method_id', 'pm.id')
            ->leftJoin('ads as ads', 'a.ads_id', 'ads.id')
            ->whereNull('a.deleted_at')
            ->where("a.uuid", '=', $id)
            ->get();

        return $res;
    }

    public function filterByAdsId($id)
    {
        $res = $this->adsPayment->from('ads_payment_method as a')
            ->select(
                'a.uuid',
                'ads.uuid as ads_id',
                'pm.uuid as pm_id',
                'pm.name as pm_name',
                'pm.slug as pm_slug',
                'pm.descr as pm_descr',
                'a.is_active',
            )
            ->leftJoin('payment_methods as pm', 'a.payment_method_id', 'pm.id')
            ->leftJoin('ads', 'a.ads_id', 'ads.id')
            ->whereNull('a.deleted_at')
            ->where("ads.uuid", '=', $id)
            ->get();

        return $res;
    }

    public function createAdPMBuy($detail)
    {
        $newEntity = new AdsPaymentMethod();
        $newEntity->uuid = $detail['uuid'];
        $newEntity->ads_id = $detail['ads_id'];
        $newEntity->payment_method_id = $detail['payment_method_id'];
        $newEntity->save();

        return $newEntity->id;
    }

    public function createAdPMSell($detail)
    {
        $newEntity = new AdsPaymentMethod();
        $newEntity->uuid = $detail['uuid'];
        $newEntity->ads_id = $detail['ads_id'];
        $newEntity->payment_method_id = $detail['payment_method_id'];
        $newEntity->payment_account_detail_id = $detail['payment_account_detail_id'];
        $newEntity->save();

        return $newEntity->id;
    }
}
