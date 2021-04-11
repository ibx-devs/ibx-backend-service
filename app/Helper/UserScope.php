<?php

namespace  App\Helper;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserScope
{
    private $role;

    public static function get($role)
    {
        //$role comes from the user_role db. Refere for the values used here.
        switch ($role) {
            case 'user':
                $res = ['user'];
                break;

            case 'super_admin':
                $res = ['*'];
                break;

            case 'admin':
                $res = ['*'];
                break;

            default:
                $res = ['user'];
                break;
        }

        return $res;
    }
}
