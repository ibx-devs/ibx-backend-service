<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\Orders;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IOrdersRepository;

class OrdersEloquentRepository extends  EloquentRepository implements IOrdersRepository
{

    public function model()
    {
        return Orders::class;
    }
}
