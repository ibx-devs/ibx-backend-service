<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\Orders;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IOrdersRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrdersEloquentRepository extends  EloquentRepository implements IOrdersRepository
{



    public $orders;
    public function __construct(Orders $orders)
    {
        parent::__construct();
        $this->orders =  $orders;
    }

    public function model()
    {
        return Orders::class;
    }

    public function findByUUID($id)
    {
        $res = $this->orders->from('orders as a')
            ->select(
                'a.id',
                'a.user_id as order_creator',
                'a.qty',
                'ads.user_id as ad_creator',
                'ads.uuid as ad_uuid',
                'ads.asset_id',
                'a.order_type',
                'a.seller_confirmed_at',
                'a.buyer_transfered_at',
                'a.cancelled_at',
                'a.deleted_at',
                'a.expire_at'
            )
            ->leftJoin('ads', 'a.ads_id', 'ads.id')
            ->where("a.uuid", '=', $id)
            ->first();

        return $res;
    }

    public function findAllDetailed()
    {
        $res = $this->orders->from('orders as a')
            ->select(
                'a.uuid',
                'u.uuid as user_id',
                'ads.uuid as ads_id',
                'u.username',
                'a.amount',
                'a.order_type',
                'a.buyer_transfered_at',
                'a.seller_confirmed_at',
                'a.expire_at',
                'a.cancelled_at',
                'a.created_at',
                'a.updated_at',
                'a.deleted_at',
                'a.visibility'
            )
            ->leftJoin('user as u', 'a.user_id', 'u.id')
            ->leftJoin('ads', 'a.ads_id', 'ads.id')
            ->get();

        return $res;
    }

    public function findOneDetailed($id)
    {
        $res = $this->orders->from('orders as a')
            ->select(
                'a.uuid',
                'u.uuid as user_id',
                'ads.uuid as ads_id',
                'u.username',
                'a.amount',
                'a.order_type',
                'a.buyer_transfered_at',
                'a.seller_confirmed_at',
                'a.expire_at',
                'a.cancelled_at',
                'a.created_at',
                'a.updated_at',
                'a.deleted_at',
                'a.visibility'
            )
            ->leftJoin('user as u', 'a.user_id', 'u.id')
            ->leftJoin('ads', 'a.ads_id', 'ads.id')
            ->where("a.uuid", '=', $id)
            ->first();

        return $res;
    }

    public function createOrder($detail)
    {
        $newEntity = new Orders();
        $newEntity->uuid = $detail['uuid'];
        $newEntity->ads_id = $detail['ads_id'];
        $newEntity->user_id = $detail['created_by'];
        $newEntity->amount = $detail['amount'];
        $newEntity->qty = $detail['qty'];
        $newEntity->order_type = $detail['order_type'];
        ($detail['order_type'] === "sell" && $detail['seller_receiving_acc_id'] !== null) ? $newEntity->seller_receiving_acc_id = $detail['seller_receiving_acc_id'] : null; //conditional/optional field
        $newEntity->save();

        return $newEntity->id;
    }

    public function cancelOrder($orderID)
    {
        return Orders::where('uuid', $orderID)
            ->update([
                'cancelled_at' => Carbon::now(),
            ]);
    }

    public function sellerConfirmReceipt($orderID)
    {
        return Orders::where('uuid', $orderID)
            ->update([
                'seller_confirmed_at' => Carbon::now(),
            ]);
    }

    public function buyerConfirmTransfer($orderID)
    {
        return Orders::where('uuid', $orderID)
            ->update([
                'buyer_transfered_at' => Carbon::now(),
            ]);
    }
}
