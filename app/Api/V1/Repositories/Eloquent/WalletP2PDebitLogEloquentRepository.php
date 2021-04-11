<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\WalletP2PDebitLog;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IWalletP2PDebitLogRepository;

class WalletP2PDebitLogEloquentRepository extends  EloquentRepository implements IWalletP2PDebitLogRepository
{

    public function model()
    {
        return WalletP2PDebitLog::class;
    }
}
