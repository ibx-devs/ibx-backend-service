<?php

namespace App\Contracts\Repository;

interface IAdsCounterpartyConditionsRepository
{
    public function filterByAdsId(string $id);

    public function findAllDetailed();

    public function findOneDetailed(string $id);
}
