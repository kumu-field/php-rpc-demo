<?php

require_once __DIR__ . '/../../RpcProtocol.php';

class RpcTcpClient
{
    /**
     * 发送数据和接收数据的超时时间  单位S
     * @var integer
     */
    const TIME_OUT = 100;

    /**
     * 异步调用发送数据前缀
     * @var string
     */
    const ASYNC_SEND_PREFIX = 'asend_';

    /**
     * 异步调用接收数据
     * @var string
     */
    const ASYNC_RECV_PREFIX = 'arecv_';

    /**
     * 到服务端的socket连接
     * @var resource
     */
    protected  $connection = null;

    protected $serviceName = '';

    protected $serviceAddress = '';

    /**
     * @param $address
     * @param $service
     * @param $method
     * @param $arguments
     * @return array|false|mixed
     */
    public function call($address, $service, $method, $arguments)
    {
        $this->serviceAddress = $address;
        $this->serviceName = $service;
        try {
            // 判断是否是异步发送
            if(0 === strpos($method, self::ASYNC_SEND_PREFIX))
            {
                $real_method = substr($method, strlen(self::ASYNC_SEND_PREFIX));
                return $this->sendData($real_method, $arguments);
            }
            // 如果是异步接受数据
            if(0 === strpos($method, self::ASYNC_RECV_PREFIX))
            {
                $real_method = substr($method, strlen(self::ASYNC_RECV_PREFIX));

                return $this->recvData();
            }
            // 同步发送接收
            $this->sendData($method, $arguments);
            $result = $this->recvData();

            if (!is_array($result)) {
                throw new \Exception('response: ' . $response);
            }
            return $result;
        }catch (\Exception $e) {
            echo 'call rpc service error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * 发送数据给服务端
     * @param string $method
     * @param array $arguments
     */
    public function sendData($method, $arguments)
    {
        try {
            $this->openConnection();
            $bin_data = RpcProtocol::encode($this->serviceName, $method, $arguments);
            if(fwrite($this->connection, $bin_data) !== strlen($bin_data))
            {
                throw new \Exception('Can not send data');
            }
            return true;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 从服务端接收数据
     * @throws Exception
     */
    public function recvData()
    {
        $header = fread($this->connection, RpcProtocol::HEADER_SIZE);

        $header = RpcProtocol::parseHeader($header);
        if (!RpcProtocol::validRequest($header['sign'] ?? '')) {
            throw new \Exception("invalid request!");
        }

        $body_buffer = fread($this->connection, intval($header['body_len']));

        if(!$body_buffer)
        {
            throw new \Exception("recvData empty");
        }
        return RpcProtocol::parseBody($body_buffer);
    }

    /**
     * 打开到服务端的连接
     * @return void
     */
    protected function openConnection()
    {
        $address = $this->serviceAddress;
        $this->connection = stream_socket_client($address, $err_no, $err_msg);
        if(!$this->connection)
        {
            throw new \Exception("can not connect to $address , $err_no:$err_msg");
        }
        stream_set_blocking($this->connection, true);
        stream_set_timeout($this->connection, self::TIME_OUT);
    }

    /**
     * 关闭到服务端的连接
     * @return void
     */
    protected function close()
    {
        if ($this->connection) {
            fclose($this->connection);
            $this->connection = null;
        }
    }
}
