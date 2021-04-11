<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\WalletSpot;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IWalletSpotRepository;

class WalletSpotEloquentRepository extends  EloquentRepository implements IWalletSpotRepository
{

    public function model()
    {
        return WalletSpot::class;
    }
}
