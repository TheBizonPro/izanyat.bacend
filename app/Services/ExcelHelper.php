<?php

namespace App\Services;

use Carbon\Carbon;

class ExcelHelper
{
    public static function convertDate(int $excelDate)
    {
        return ($excelDate - 25569) * 86400;
    }

    public static function todmY($excelDate)
    {
        return Carbon::createFromTimestamp(self::convertDate($excelDate))->format('d.m.Y');
    }
}
