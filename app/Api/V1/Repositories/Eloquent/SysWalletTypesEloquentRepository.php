<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\SysWalletTypes;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\ISysWalletTypesRepository;

class SysWalletTypesEloquentRepository extends  EloquentRepository implements ISysWalletTypesRepository
{

    public function model()
    {
        return SysWalletTypes::class;
    }
}
