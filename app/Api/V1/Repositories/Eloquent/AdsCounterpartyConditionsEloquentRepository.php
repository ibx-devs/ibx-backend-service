<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\AdsCounterpartyConditions;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IAdsCounterpartyConditionsRepository;

class AdsCounterpartyConditionsEloquentRepository extends  EloquentRepository implements IAdsCounterpartyConditionsRepository
{

    private $adsCPC;

    public function __construct(AdsCounterpartyConditions $adsCPC)
    {
        parent::__construct();
        $this->adsCPC = $adsCPC;
    }


    public function model()
    {
        return AdsCounterpartyConditions::class;
    }

    public function findAllDetailed()
    {
        $res = $this->adsCPC->from('ads_counterparty_conditions as a')
            ->select(
                'a.uuid',
                'ads.uuid',
                'a.check_completed_kyc',
                'a.check_holding_btc',
                'a.check_days_joined',
                'a.is_active',
            )
            ->leftJoin('ads', 'a.ads_id', 'ads.id')
            ->where('is_active', '=', 1)
            ->get();

        return $res;
    }

    public function findOneDetailed($id)
    {
        $res = $this->adsCPC->from('ads_counterparty_conditions as a')
            ->select(
                'a.uuid',
                'ads.uuid',
                'a.check_completed_kyc',
                'a.check_holding_btc',
                'a.check_days_joined',
                'a.is_active',
            )
            ->leftJoin('ads', 'a.ads_id', 'ads.id')
            ->where('a.is_active', '=', 1)
            ->where('a.uuid', '=', $id)
            ->first();

        return $res;
    }

    public function filterByAdsId($adsID)
    {
        $res = $this->adsCPC->from('ads_counterparty_conditions as a')
            ->select(
                'a.uuid',
                'ads.uuid',
                'a.check_completed_kyc',
                'a.check_holding_btc',
                'a.check_days_joined',
                'a.is_active',
            )
            ->leftJoin('ads', 'a.ads_id', 'ads.id')
            ->where('a.is_active', '=', 1)
            ->where('ads.uuid', '=', $adsID)
            ->get();

        return $res;
    }

    public function createAdCPCondition($detail)
    {
        $newEntity = new AdsCounterpartyConditions();
        $newEntity->uuid = $detail['uuid'];
        $newEntity->ads_id = $detail['ads_id'];
        $detail['check_holding_btc'] !== null ? $newEntity->check_holding_btc = $detail['check_holding_btc'] : null;
        $detail['check_days_joined'] !== null ? $newEntity->check_days_joined = $detail['check_days_joined'] : null;
        $newEntity->save();

        return $newEntity->id;
    }
}
