<?php

require_once __DIR__ . '/RpcTcpClient.php';

class RpcClient
{
    public $addressArray;

    protected $serviceName = '';

    protected static $instance;

    public function __construct() {
        $this->addressArray = [
            '127.0.0.1:6969'
        ];
    }

    public static function instance() {
        if (self::$instance instanceof RpcClient) {
            return self::$instance;
        }
        self::$instance = new self();
        return self::$instance;
    }

    public function getServiceAddress() {
        if ($this->addressArray) {
            return $this->addressArray[array_rand($this->addressArray)] ?: null;
        }
        return null;
    }

    /**
     * 设置服务名
     * @param $serviceName
     */
    public function service($serviceName)
    {
        $this->serviceName = $serviceName;
        return $this;
    }

    /**
     * 调用
     * @param $method
     * @param $arguments
     * @return bool|mixed
     */
    public function __call($method, $arguments)
    {
        $serviceAddress = $this->getServiceAddress();
        if (empty($serviceAddress)) {
            return false;
        }
        $client = new RpcTcpClient();
        return $client->call($serviceAddress, $this->serviceName, $method, $arguments);
    }
}
