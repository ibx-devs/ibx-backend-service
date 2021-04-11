<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\UserRole;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IUserRoleRepository;

class UserRoleEloquentRepository extends  EloquentRepository implements IUserRoleRepository
{

    public function model()
    {
        return UserRole::class;
    }
}
