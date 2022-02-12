<?php

namespace  App\Utils;

use Carbon\Carbon;
use Countable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderMapper extends BaseMapper
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

    public static function createOrderInputData($data)
    {
        $data = (object) $data;
        return [
            'order_type' => SELF::propExist($data, 'order_type'),
            'ads_id' => SELF::propExist($data, 'ad_internal_id'),
            'uuid' => SELF::propExist($data, 'uuid'),
            'amount' => SELF::propExist($data, 'amount'),
            'seller_receiving_acc_id' => SELF::propExist($data, 'payment_acc_internal_id'),
            'qty' => SELF::propExist($data, 'qty'),
            'created_by' => SELF::propExist($data, 'user_id'),
        ];
    }
}
