<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\UserPaymentAccountDetail;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IUserPaymentAccountDetail;

class UserPaymentAccountDetailEloquentRepository extends  EloquentRepository implements IUserPaymentAccountDetail
{
    private $UPAD;

    public function __construct(UserPaymentAccountDetail $UPAD)
    {
        parent::__construct();
        $this->UPAD = $UPAD;
    }

    public function model()
    {
        return UserPaymentAccountDetail::class;
    }

    public function getIDsByUUID($uuid, $userID)
    {
        $res = $this->UPAD->select('id', 'payment_method_id')
            ->where('uuid', '=', $uuid)
            ->where('user_id', '=', $userID)
            ->first();
        return $res;
    }
}
