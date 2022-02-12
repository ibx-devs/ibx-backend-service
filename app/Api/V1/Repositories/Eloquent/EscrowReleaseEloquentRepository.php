<?php

namespace App\Api\V1\Repositories\Eloquent;

use App\Api\V1\Models\EscrowRelease;
use App\Api\V1\Repositories\EloquentRepository;
use App\Contracts\Repository\IEscrowReleaseRepository;

class EscrowReleaseEloquentRepository extends  EloquentRepository implements IEscrowReleaseRepository
{

    public $escrowRelease;
    public function __construct(EscrowRelease $escrowRelease)
    {
        parent::__construct();
        $this->escrowRelease =  $escrowRelease;
    }


    public function model()
    {
        return EscrowRelease::class;
    }


    public function create($detail)
    {
        $newEntity = new EscrowRelease();
        $newEntity->uuid = $detail['uuid'];
        $newEntity->escrow_id = $detail['escrow_id'];
        $newEntity->amount = $detail['amount'];
        $newEntity->balance = $detail['amount'];
        $newEntity->wallet_type = $detail['wallet_type'];
        $newEntity->wallet_id = $detail['wallet_id'];
        $newEntity->save();

        return $newEntity->id;
    }
}
