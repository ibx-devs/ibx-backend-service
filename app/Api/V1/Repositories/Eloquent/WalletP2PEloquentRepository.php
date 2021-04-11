<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\WalletP2P;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IWalletP2PRepository;

class WalletP2PEloquentRepository extends  EloquentRepository implements IWalletP2PRepository
{

    public function model()
    {
        return WalletP2P::class;
    }
}
