<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\SysCurrency;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\ISysCurrencyRepository;

class SysCurrencyEloquentRepository extends  EloquentRepository implements ISysCurrencyRepository
{

    public $sysCurr;
    public function __construct(SysCurrency $sysCurr)
    {
        parent::__construct();
        $this->sysCurr =  $sysCurr;
    }


    public function model()
    {
        return SysCurrency::class;
    }

    public function getP2PLocal()
    {
        $res = $this->sysCurr->from('sys_currency as a')
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
        $res = $this->sysCurr->from('sys_currency as a')
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
        $res = $this->sysCurr->select('id')
            ->where('slug', '=', $slug)
            ->first();
        return $res;
    }
}
