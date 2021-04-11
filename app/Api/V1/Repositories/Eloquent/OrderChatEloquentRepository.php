<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\OrderChat;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IOrderChatRepository;

class OrderChatEloquentRepository extends  EloquentRepository implements IOrderChatRepository
{

    public function model()
    {
        return OrderChat::class;
    }
}
