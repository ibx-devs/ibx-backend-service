<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\OrderCancelled;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IOrderCancelledRepository;

class OrderCancelledEloquentRepository extends  EloquentRepository implements IOrderCancelledRepository
{

    public function model()
    {
        return OrderCancelled::class;
    }



    public $orders;
    public function __construct(OrderCancelled $orders)
    {
        parent::__construct();
        $this->orders =  $orders;
    }



    public function create($detail)
    {
        $newEntity = new OrderCancelled();
        $newEntity->uuid = $detail['uuid'];
        $newEntity->order_id = $detail['order_id'];
        $newEntity->user_id = $detail['created_by'];
        $newEntity->comment = $detail['comment'];
        $newEntity->save();

        return $newEntity->id;
    }
}
