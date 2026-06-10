<?php

namespace App\Enums;

class UserTypeEnum extends BaseEnum
{
    const SYSTEM = 1;
    const CUSTOM = 2;

    public static function list($withText = false)
    {
        return $withText ?
            [
                self::SYSTEM => 'system',
                self::CUSTOM => 'custom'
            ] :
            [
                self::SYSTEM,
                self::CUSTOM
            ];
    }
}
