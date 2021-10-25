<?php

class Response {

    public static function returnResponse($code, $data = null, $message = ''): array
    {
        return [
            'code' => $code,
            'msg' => $message ?: Message::getMessage($code),
            'data' => $data
        ];
    }

    /**
     * @param $data
     * @return array
     */
    public static function success($data): array
    {
        return self::returnResponse(Message::STATUS_OK, $data);
    }

    public static function error($code, $message = ''): array
    {
        return self::returnResponse($code, null, $message);
    }
}