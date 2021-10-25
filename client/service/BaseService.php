<?php

class BaseService
{
    protected static $instance;

    public static function instance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        self::$instance = new static();
        return self::$instance;
    }
}
