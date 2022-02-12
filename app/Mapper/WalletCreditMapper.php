<?php

namespace  App\Utils;

use Carbon\Carbon;
use Countable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletCreditMapper extends BaseMapper
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

    public static function createWalletCreditInputData($data)
    {
        $data = (object) $data;
        return [
            'uuid' => SELF::propExist($data, 'uuid'),
            'wallet_id' => SELF::propExist($data, 'wallet_id'),
            'escrow_id' => SELF::propExist($data, 'escrow_id'),
            'order_id' => SELF::propExist($data, 'order_id'),
            'amount' => SELF::propExist($data, 'amount'),
            'cashflow_channel_id' => SELF::propExist($data, 'cashflow_channel_id'),
            'user_id' => SELF::propExist($data, 'user_id'),
            'network_type_id' => SELF::propExist($data, 'network_type_id'),
            'wallet_address' => SELF::propExist($data, 'address'),
            'txn_id' => SELF::propExist($data, 'txn_id'),
        ];
    }
}
