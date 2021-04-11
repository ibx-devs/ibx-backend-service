<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\SysWalletCashflowChannels;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\ISysWalletCashflowChannelsRepository;

class SysWalletCashflowChannelsEloquentRepository extends  EloquentRepository implements ISysWalletCashflowChannelsRepository
{

    public function model()
    {
        return SysWalletCashflowChannels::class;
    }
}
