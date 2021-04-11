<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\SysActivityTypes;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\ISysActivityTypesRepository;

class SysActivityTypesEloquentRepository extends  EloquentRepository implements ISysActivityTypesRepository
{

    public function model()
    {
        return SysActivityTypes::class;
    }
}
