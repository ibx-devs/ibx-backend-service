<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\WalletTypes;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IWalletTypesRepository;

class WalletTypesEloquentRepository extends  EloquentRepository implements IWalletTypesRepository
{

    public function model()
    {
        return WalletTypes::class;
    }
}
