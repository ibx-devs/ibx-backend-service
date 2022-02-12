<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\AdsVisit;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IAdsVisit;
use Illuminate\Support\Facades\DB;

class AdsVisitEloquentRepository extends  EloquentRepository implements IAdsVisit
{

    public $adsVisit;
    public function __construct(AdsVisit $adsVisit)
    {
        parent::__construct();
        $this->adsVisit =  $adsVisit;
    }

    public function model()
    {
        return AdsVisit::class;
    }

    public function findAllDetailed()
    {
        $res = $this->adsVisit->from('ads_visit as a')
            ->select(
                'a.uuid',
                'u.uuid as visitor_id',
                'u.username as visitor_username',
                'a.created_at',
                'a.updated_at',
                'a.deleted_at',
                'a.visibility'
            )
            ->leftJoin('user as u', 'a.visitor_id', 'u.id')
            ->get();

        return $res;
    }

    public function findOneDetailed($id)
    {
        $res = $this->adsVisit->from('ads_visit as a')
            ->select(
                'a.uuid',
                'u.uuid as visitor_id',
                'u.username as visitor_username',
                'a.created_at',
                'a.updated_at',
                'a.deleted_at',
                'a.visibility'
            )
            ->leftJoin('user as u', 'a.visitor_id', 'u.id')
            ->where("a.uuid", '=', $id)
            ->first();

        return $res;
    }

    public function createAdVisit($detail)
    {
        $newEntity = new AdsVisit();
        $newEntity->uuid = $detail['uuid'];
        $newEntity->ad_id = $detail['ad_id'];
        $newEntity->visitor_id = $detail['visitor_id'];
        $newEntity->save();

        return $newEntity->id;
    }
}
