<?php

namespace  App\Utils;

use Carbon\Carbon;
use Countable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserMapper extends BaseMapper
{

    public static function prune($data)
    {
        if (!$data instanceof Countable) {
            unset(
                $data['password'],
                $data['role'],
            );
            return $data;
        }

        foreach ($data as $entity) {
            unset(
                $entity['password'],
                $entity['role'],
            );
        }

        return $data;
    }
}
