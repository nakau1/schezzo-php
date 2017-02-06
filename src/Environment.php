<?php

namespace app;

/**
 * Class Environment
 * @package app
 */
class Environment
{
    /** @var array */
    protected static $environmentConfig;

    /**
     * @return array
     */
    public static function get()
    {
        if (!static::$environmentConfig) {
            static::$environmentConfig = require(__DIR__ . '/../environment/conf.php');
        }
        return static::$environmentConfig;
    }
}
