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
}
