<?php

namespace app\helpers;

use Yii;
use yii\helpers\FileHelper;

/**
 * Class File
 * 頻度の高いファイル処理を行う
 * @package app\helpers
 */
class File
{
    /**
     * @param string $path
     */
    public static function makeDirectoryIfNotExists(string $path)
    {
        if (!file_exists($path)) {
            FileHelper::createDirectory($path);
        }
    }
}
