<?php

namespace  App\Utils;

use Carbon\Carbon;
use Countable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdsMapper extends BaseMapper
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

    public static function createAdsInputData($data)
    {
        $data = (object) $data;
        return [
            'ad_type' => SELF::propExist($data, 'ad_type'),
            'uuid' => SELF::propExist($data, 'uuid'),
            'qty' => SELF::propExist($data, 'qty'),
            'price' => SELF::propExist($data, 'price'),
            'min_order' => SELF::propExist($data, 'min_order'),
            'max_order' => SELF::propExist($data, 'max_order'),
            'is_offline' => SELF::propExist($data, 'offline'),
            'remarks' => SELF::propExist($data, 'remarks'),
            'asset_id' => SELF::propExist($data, 'asset'),
            'with_fiat_id' => SELF::propExist($data, 'with_fiat'),
            'payment_time_limit_id' => SELF::propExist($data, 'payment_timeout'),
            'created_by' => SELF::propExist($data, 'user_id'),
        ];
    }
}
