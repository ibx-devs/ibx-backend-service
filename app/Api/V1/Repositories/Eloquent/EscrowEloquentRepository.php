<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\Escrow;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IEscrowRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EscrowEloquentRepository extends  EloquentRepository implements IEscrowRepository
{

    public $ads;
    public function __construct(Escrow $ads)
    {
        parent::__construct();
        $this->ads =  $ads;
    }

    public function model()
    {
        return Escrow::class;
    }


    public function findByOrderId($orderID, $userID)
    {
        $res = DB::table('escrow as a')
            ->select(
                'a.id',
                'a.uuid',
                'a.amount',
                'a.balance',
            )
            ->where("a.seller_id", '=', $userID)
            ->where("a.order_id", '=', $orderID)
            ->lockForUpdate()
            ->first();

        return $res;
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

    public function create($detail)
    {
        $newEntity = new Escrow();
        $newEntity->uuid = $detail['uuid'];
        $newEntity->seller_id = $detail['seller_id'];
        $newEntity->amount = $detail['qty'];
        $newEntity->balance = $detail['qty'];
        $newEntity->ad_id = $detail['ad_id'];
        $newEntity->escrow_type = $detail['escrow_type'];
        $newEntity->wallet_type = $detail['wallet_type'];
        $newEntity->wallet_id = $detail['wallet_id'];
        $detail['order_id'] !== null ? $newEntity->order_id = $detail['order_id'] : null; //conditional/optional field
        $newEntity->save();

        return $newEntity->id;
    }


    public function releaseCoinByOrderId($amount, $orderID, $userID)
    {
        return Escrow::where('order_id', $orderID)
            ->where('seller_id', $userID)
            ->update([
                'balance' => DB::raw("balance - {$amount}"),
                'last_released_at' => Carbon::now(),
            ]);
    }

    public function creditWallet($walletUUID, $amount)
    {
        return Escrow::where('uuid', $walletUUID)
            ->update([
                'amount' => DB::raw("amount + {$amount}"),
                'last_amount_credited' => "{$amount}",
            ]);
    }
}
