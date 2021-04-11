<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\UserVerification;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IUserVerificationRepository;

class UserVerificationEloquentRepository extends  EloquentRepository implements IUserVerificationRepository
{

    public function model()
    {
        return UserVerification::class;
    }
}
