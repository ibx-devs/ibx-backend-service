<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\WalletP2PCreditLog;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IWalletP2PCreditLogRepository;

class WalletP2PCreditLogEloquentRepository extends  EloquentRepository implements IWalletP2PCreditLogRepository
{

    public function model()
    {
        return WalletP2PCreditLog::class;
    }
}
