<?php
namespace app\helpers;

use Carbon\Carbon;

/**
 * Class DateUtil
 * @package app\helpers
 */
class Date extends Carbon
{
    const DATETIME_FORMAT = self::DEFAULT_TO_STRING_FORMAT;
    const DATE_FORMAT     = 'php:Y-m-d';

    const DISPLAY_DATE_FORMAT = 'php:Y年m月d日';
    const DISPLAY_ADMIN_INFORMATION_DATTIME_FORMAT = 'php:Y年m月d日 G時';
}