<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\WalletP2PDebitLog;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IWalletP2PDebitLogRepository;
use Illuminate\Support\Facades\DB;

class WalletP2PDebitLogEloquentRepository extends  EloquentRepository implements IWalletP2PDebitLogRepository
{

    public $ads;
    public function __construct(WalletP2PDebitLog $ads)
    {
        parent::__construct();
        $this->ads =  $ads;
    }

    public function model()
    {
        return WalletP2PDebitLog::class;
    }


    public function getWalletInfo($userID, $assetID)
    {
        $res = DB::table('wallet_p2p as a')
            ->select(
                'a.id',
                'a.uuid',
                'a.user_id as owner',
                'a.amount',
            )
            ->where("a.user_id", '=', $userID)
            ->where("a.asset_id", '=', $assetID)
            ->lockForUpdate()
            ->first();

        return $res;
    }

    // public function getLockedAdInfo($id)
    // {
    //     $res = $this->ads->from('ads as a')
    //         ->select('a.id', 'a.ad_type', 'a.qty', 'a.user_id as creator', 'a.min_order', 'a.max_order')
    //         ->where("a.uuid", '=', $id)
    //         ->lockForUpdate()
    //         ->first();

    //     return $res;
    // }


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
        $newEntity = new WalletP2PDebitLog();
        $newEntity->uuid = $detail['uuid'];
        $newEntity->wallet_id = $detail['wallet_id'];
        $newEntity->user_id = $detail['created_by'];
        $newEntity->amount = $detail['qty'];
        $newEntity->cashflow_channel_id = $detail['cashflow_channel_id'];
        $detail['order_id'] !== null ? $newEntity->order_id = $detail['order_id'] : null; //conditional/optional field
        $detail['escrow_id'] !== null ? $newEntity->escrow_id = $detail['escrow_id'] : null; //conditional/optional field
        $newEntity->save();

        return $newEntity->id;
    }


    public function debitWallet($userID, $assetID, $amount)
    {
        return WalletP2PDebitLog::where('user_id', $userID)
            ->where('asset_id', $assetID)
            ->update([
                'amount' => DB::raw("amount - {$amount}"),
                'last_amount_debited' => "{$amount}",
            ]);
    }

    public function creditWallet($userID, $assetID, $amount)
    {
        return WalletP2PDebitLog::where('user_id', $userID)
            ->where('asset_id', $assetID)
            ->update([
                'amount' => DB::raw("amount + {$amount}"),
                'last_amount_credited' => "{$amount}",
            ]);
    }
}
