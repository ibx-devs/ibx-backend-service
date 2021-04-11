<?php

namespace  App\Utils;

use Carbon\Carbon;
use Countable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdsCPCMapper extends BaseMapper
{

    public static function propExist($object, $prop)
    {
        return property_exists($object, $prop) ? $object->{$prop} : null;
    }

    public static function pruneDetailed($data)
    {
        if (!$data instanceof Countable) {
            unset(
                $data['visibility'],
            );
            return $data;
        }

        foreach ($data as $entity) {
            unset(
                $entity['visibility'],
            );
        }

        return $data;
    }

    public static function prune($data)
    {

        if (!$data instanceof Countable) {
            unset(
                $data['created_at'],
                $data['updated_at'],
            );
            return $data;
        }

        foreach ($data as $entity) {
            unset(
                $entity['created_at'],
                $entity['updated_at'],
            );
        }

        return $data;
    }

    public static function createAdsCPCInputData($data)
    {
        $data = (object) $data;
        return [
            'uuid' => SELF::propExist($data, 'cpc_uuid'),
            'ads_id' => SELF::propExist($data, 'ads_id'),
            'check_holding_btc' => SELF::propExist($data, 'holding_btc'),
            'check_days_joined' => SELF::propExist($data, 'days_joined'),
        ];
    }
}
