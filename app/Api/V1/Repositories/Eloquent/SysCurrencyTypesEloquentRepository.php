<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\SysCurrencyTypes;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\ISysCurrencyTypesRepository;

class SysCurrencyTypesEloquentRepository extends  EloquentRepository implements ISysCurrencyTypesRepository
{

    public function model()
    {
        return SysCurrencyTypes::class;
    }
}
