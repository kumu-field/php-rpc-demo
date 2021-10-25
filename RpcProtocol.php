<?php

class RpcProtocol {
    /**
     * 分隔符
     */
    const DELIMITER = "\r\n";
    const HEADER_SIZE = 40;

    /**
     * 密钥
     * @return string
     */
    protected static function secretKey() {
        return md5('rpctest');
    }

    /**
     * @param $body_len
     * @return string
     */
    public static function packHeader($body_len) {
        $token = self::secretKey();
        return pack('a32a8', $token, $body_len);  // 32位字符串 + 8位字符串
    }

    /**
     * @param $header
     * @return array|false
     */
    public static function parseHeader($header) {
        return unpack('a32token/a8body_len', $header);
    }

    /**
     * @param $token
     * @return bool
     */
    public static function validRequest($token) {
        return true;
        return $token == self::secretKey();
    }

    /**
     * 将数据打包成 Rpc 协议数据
     * @param $data
     * @return string
     */
    public static function pack($data): string
    {
        $body = self::packBody($data);
        $body_len = strlen($body);
        $header = self::packHeader($body_len);
        return $header . $body;
    }

    /**
     * 解析 Rpc 协议数据
     * @param $data
     * @return mixed
     */
    public static function parseBody($data)
    {
        return json_decode(trim($data), true);
    }

    /**
     * 打包 Rpc 协议数据
     * @param $data
     * @return mixed
     */
    public static function packBody($data)
    {
        return json_encode($data) . self::DELIMITER;
    }

    /**
     * 封装要调用的类、方法、及参数
     * @param $class
     * @param $method
     * @param $params
     */
    public static function encode($class, $method, $params) {
        $data = [
            'class' => $class,
            'method' => $method,
            'params' => $params
        ];
        return self::pack($data);
    }

    /**
     * 解析Rpc协议数据, 获得要调用的类、方法、及参数
     * @param $buff
     * @param $class
     * @param $method
     * @param $params
     */
    public static function decode($buff, &$class, &$method, &$params) {
        // 解析Rpc协议数据
        $data = self::parseBody($buff);
        // 获得要调用的类、方法、及参数
        $class = $data['class'] ?? '';
        $method = $data['method'] ?? '';
        $params = $data['params'] ?? '';
    }
}