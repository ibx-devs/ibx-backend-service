<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\SysSettings;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\ISysSettingsRepository;

class SysSettingsEloquentRepository extends  EloquentRepository implements ISysSettingsRepository
{

    public function model()
    {
        return SysSettings::class;
    }
}
