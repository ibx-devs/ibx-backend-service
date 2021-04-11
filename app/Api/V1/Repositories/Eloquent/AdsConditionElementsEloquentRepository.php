<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\AdsConditionElements;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IAdsConditionElementsRepository;

class AdsConditionElementsEloquentRepository extends  EloquentRepository implements IAdsConditionElementsRepository
{

    public function model()
    {
        return AdsConditionElements::class;
    }
}
