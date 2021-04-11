<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\Escrow;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IEscrowRepository;

class EscrowEloquentRepository extends  EloquentRepository implements IEscrowRepository
{

    public function model()
    {
        return Escrow::class;
    }
}
