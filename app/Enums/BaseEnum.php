<?php

namespace App\Enums;

use stdClass;

abstract class BaseEnum
{
    public abstract static function list($withText = false);

    public static function getLabel($code)
    {
        $labels = static::list(true);
        if (isset($labels[$code])) {
            return $labels[$code];
        }
        return null;
    }

    public static function getData($code)
    {
        return [
            'name' => self::getLabel($code),
            'code' => $code
        ];
    }

    public static function addLabel($types, $key = 'name', $value = 'type')
    {
        if (is_null($types)) {
            return [];
        }
        $list = static::list(true);
        try {
            if (is_string($types)) {
                $types = json_decode($types);
            }
            return array_map(function ($item) use ($list, $key, $value) {
                if ($item instanceof stdClass) {
                    $item->{$key} = $list[$item->{$value}];
                } else {
                    $item[$key] = $list[$item[$value]];
                }
                return $item;
            }, $types);
        } catch (\Throwable $th) {
            return ['error' => $th->getMessage()];
        }
    }

    public static function toArray()
    {
        $codes = static::list();
        if ($c = request('codes')) {
            $codes = explode(',', $c);
        }
        self::setLocaleFromQuery();
        $data = [];
        foreach (static::list(true) as $key => $value) {
            if (in_array($key, $codes)) {
                $data[] = [
                    'code' => $key,
                    'name' => $value,
                ];
            }
        }
        return $data;
    }

    protected static function setLocaleFromQuery()
    {
        if ($code = request('lang')) {
            app()->setLocale($code);
        }
    }

    public static function in()
    {
        return 'in:' . implode(',', static::list());
    }

    public static function getHashmap()
    {
        return collect(static::list(true))->reduce(function ($acc, $item, $key) {
            $acc[$item] = $key;
            return $acc;
        }, []);
    }
}
