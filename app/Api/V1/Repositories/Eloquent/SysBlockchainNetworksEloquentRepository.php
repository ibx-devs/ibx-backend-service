<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\SysBlockchainNetworks;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\ISysBlockchainNetworks;

class SysBlockchainNetworksEloquentRepository extends  EloquentRepository implements ISysBlockchainNetworks
{

    public $sysBlockchainNet;
    public function __construct(SysBlockchainNetworks $sysBlockchainNet)
    {
        parent::__construct();
        $this->sysBlockchainNet =  $sysBlockchainNet;
    }


    public function model()
    {
        return SysBlockchainNetworks::class;
    }

    public function getP2PLocal()
    {
        $res = $this->sysBlockchainNet->from('sys_currency as a')
            ->select(
                'a.uuid',
                'a.slug',
                'a.symbol',
            )
            ->whereNull('a.deleted_at')
            ->whereNotNull('a.visibility')
            ->where("a.p2p_local", '=', '1')
            ->get();

        return $res;
    }

    public function getP2PForeign()
    {
        $res = $this->sysBlockchainNet->from('sys_currency as a')
            ->select(
                'a.uuid',
                'a.slug',
                'a.symbol',
            )
            ->whereNull('a.deleted_at')
            ->whereNotNull('a.visibility')
            ->where("a.p2p_foreign", '=', '1')
            ->get();

        return $res;
    }

    public function slugToID($slug)
    {
        $res = $this->sysBlockchainNet->select('id')
            ->where('slug', '=', $slug)
            ->first();
        return $res;
    }
}
