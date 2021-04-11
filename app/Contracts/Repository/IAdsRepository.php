<?php

namespace App\Contracts\Repository;

use App\Contracts\IRepository;

interface IAdsRepository extends IRepository
{
    public function findAllDetailed();

    public function findOneDetailed(string $id);
}
