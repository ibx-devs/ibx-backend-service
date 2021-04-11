<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\Ads;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IAdsRepository;

class AdsEloquentRepository extends  EloquentRepository implements IAdsRepository
{

    public $ads;
    public function __construct(Ads $ads)
    {
        parent::__construct();
        $this->ads =  $ads;
    }

    public function model()
    {
        return Ads::class;
    }

    public function findAllDetailed()
    {
        $res = $this->ads->from('ads as a')
            ->select(
                'a.uuid',
                'u.uuid as user_id',
                'u.username',
                'a.qty',
                'a.price',
                'a.min_order',
                'a.max_order',

                'asset.uuid as asset_id',
                'asset.name as asset_name',
                'asset.slug as asset_slug',
                'asset.symbol as asset_symbol',
                'asset.icon as asset_icon',

                'fiat.uuid as fiat_id',
                'fiat.name as fiat_name',
                'fiat.slug as fiat_slug',
                'fiat.symbol as fiat_symbol',
                'fiat.icon as fiat_icon',

                'tl.uuid as time_id',
                'tl.value as time_value',
                'tl.slug as time_slug',
                'tl.descr as time_descr',

                'a.is_offline',
                'a.completed_count',
                'a.remarks',
                'a.ad_type',
                'a.created_at',
                'a.updated_at',
                'a.deleted_at',
                'a.visibility'
            )
            ->leftJoin('user as u', 'a.user_id', 'u.id')
            ->leftJoin('sys_currency as asset', 'a.asset_id', 'asset.id')
            ->leftJoin('sys_currency as fiat', 'a.with_fiat_id', 'fiat.id')
            ->leftJoin('ads_payment_time_limit as tl', 'a.payment_time_limit_id', 'tl.id')
            // ->where("a.username", '=', $username)
            ->get();

        return $res;
    }

    public function findOneDetailed($id)
    {
        $res = $this->ads->from('ads as a')
            ->select(
                'a.uuid',
                'u.uuid as user_id',
                'u.username',
                'a.qty',
                'a.price',
                'a.min_order',
                'a.max_order',

                'asset.uuid as asset_id',
                'asset.name as asset_name',
                'asset.slug as asset_slug',
                'asset.symbol as asset_symbol',
                'asset.icon as asset_icon',

                'fiat.uuid as fiat_id',
                'fiat.name as fiat_name',
                'fiat.slug as fiat_slug',
                'fiat.symbol as fiat_symbol',
                'fiat.icon as fiat_icon',

                'tl.uuid as time_id',
                'tl.value as time_value',
                'tl.slug as time_slug',
                'tl.descr as time_descr',

                'a.is_offline',
                'a.completed_count',
                'a.remarks',
                'a.ad_type',
                'a.created_at',
                'a.updated_at',
                'a.deleted_at',
                'a.visibility'
            )
            ->leftJoin('user as u', 'a.user_id', 'u.id')
            ->leftJoin('sys_currency as asset', 'a.asset_id', 'asset.id')
            ->leftJoin('sys_currency as fiat', 'a.with_fiat_id', 'fiat.id')
            ->leftJoin('ads_payment_time_limit as tl', 'a.payment_time_limit_id', 'tl.id')
            ->where("a.uuid", '=', $id)
            ->first();

        return $res;
    }

    public function createAd($detail)
    {
        $newEntity = new Ads();
        $newEntity->uuid = $detail['uuid'];
        $newEntity->ad_type = $detail['ad_type'];
        $newEntity->user_id = $detail['created_by'];
        $newEntity->qty = $detail['qty'];
        $newEntity->price = $detail['price'];
        $newEntity->min_order = $detail['min_order'];
        $newEntity->max_order = $detail['max_order'];
        $detail['is_offline'] !== null ? ($newEntity->is_offline = $detail['is_offline'] === true ? 1 : null) : null; //conditional/optional field
        $detail['remarks'] !== null ? $newEntity->remarks = $detail['remarks'] : null; //conditional/optional field
        $newEntity->asset_id = $detail['asset_id'];
        $newEntity->with_fiat_id = $detail['with_fiat_id'];
        $newEntity->payment_time_limit_id = $detail['payment_time_limit_id'];
        $newEntity->save();

        return $newEntity->id;
    }
}
