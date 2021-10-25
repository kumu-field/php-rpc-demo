<?php

class Message {
    const STATUS_OK = 200;
    const SYSTEM_ERROR = 500;
    const CLASS_OR_METHOD_ERROR = 1000;
    const USER_ID_EMPTY = 10000;

    /**
     * @param $code
     * @return string
     */
    public static function getMessage($code): string
    {
        $package = self::package();
        if (!array_key_exists($code, $package)) {
            return $code;
        }
        return $package[$code];
    }

    /**
     * @return string[]
     */
    protected static function package(): array
    {
        return [
            self::STATUS_OK => 'ok',
            self::SYSTEM_ERROR => 'system error',
            self::CLASS_OR_METHOD_ERROR => 'class or method error',
            self::USER_ID_EMPTY => 'user_id empty'
        ];
    }
}