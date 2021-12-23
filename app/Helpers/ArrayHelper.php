<?php

namespace App\Helpers;

class ArrayHelper
{
    public static function flattenJoin($item, $key=null): array
    {
        $strKey = ($key ?? '');

        if (! is_array($item))
            return [$item];


        $joinedStrings = [];

        foreach ($item as $key=>$value) {

            if (is_numeric($key))
                $key = null;

            $paths = self::flattenJoin($value, $key);

            foreach ($paths as $path) {
                $glue = '.';
                if ($strKey === '' || $strKey === null)
                    $glue = '';

                $joinedStrings[] = $strKey . $glue . $path;
            }
        }

        return $joinedStrings;
    }

    public static function atLeastOneKeyExists(array $keys, array $array)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $array))
                return true;
        }

        return false;
    }

    public static function keysExists(array $keys, array $array)
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $array))
                return false;
        }

        return true;
    }

    public static function isFlat(array $array)
    {
        foreach ($array as $item) {
            if (is_array($item))
                return false;
        }

        return true;
    }
}
