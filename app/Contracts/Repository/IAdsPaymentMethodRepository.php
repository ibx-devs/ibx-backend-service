<?php

namespace App\Contracts\Repository;

use App\Contracts\IRepository;

interface IAdsPaymentMethodRepository extends IRepository
{
    public function filterByAdsId(string $id);
}
