<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\AdsConditionTypes;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IAdsConditionTypesRepository;

class AdsConditionTypesEloquentRepository extends  EloquentRepository implements IAdsConditionTypesRepository
{

    public function model()
    {
        return AdsConditionTypes::class;
    }
}
