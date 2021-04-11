<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\EscrowRelease;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IEscrowReleaseRepository;

class EscrowReleaseEloquentRepository extends  EloquentRepository implements IEscrowReleaseRepository
{

    public function model()
    {
        return EscrowRelease::class;
    }
}
