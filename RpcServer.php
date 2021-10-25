<?php


class RpcServer {
    // 闭包函数
    public $onCall = null;
    public $onClose = null;

    /**
     * 创建tcp连接的基本配置
     */
    private $params = [
        'host'  => '',  // ip地址
        'port'  => '', // 端口
    ];

    /**
     * 发送数据和接收数据的超时时间 单位S
     * @var integer
     */
    const TIME_OUT = 5;

    private $server = null;

    /**
     * RpcServer constructor.
     * @param $params
     */
    public function __construct($params)
    {
        $this->init($params);
    }

    /**
     * 初始化必要参数
     * @param $params
     */
    private function init($params) {
        // 将传递过来的参数初始化
        $this->params = $params;
        // 创建tcpsocket服务
        $this->createServer();
    }

    /**
     * 创建tcp socket服务
     */
    private function createServer() {
        if (empty($this->params['host']) || empty($this->params['port'])) {
            exit("socket server host or port empty!");
        }
        $address = "tcp://{$this->params['host']}:{$this->params['port']}";
        $this->server = stream_socket_server($address, $err_no, $err_msg);
        if (!$this->server) exit([
            $err_no, $err_msg
        ]);
        echo "create tcp server success, pid: " . getmypid() . ", address: $address\n";
    }

    /**
     * 返回当前对象
     * @param $params
     * @return RpcServer
     */
    public static function instance($params): RpcServer
    {
        return new RpcServer($params);
    }

    /**
     * 运行
     */
    public function run() {
        // 同步阻塞
       while (true) {
           $client = @stream_socket_accept($this->server, self::TIME_OUT);
           if ($client) {
               if (is_callable($this->onCall)) {
                   ($this->onCall)($this, $client);
               }
           }
       }
    }

    /**
     * @param $client
     * @param $size
     * @return false|string
     */
    public function receive($client, $size) {
        return fread($client, $size);
    }

    /**
     * 发送数据
     * @param $client
     * @param $data
     */
    public function send($client, $data) {
        fwrite($client, $data);
    }

    /**
     * 关闭客户端
     * @param $client
     */
    public function close($client) {
        //关闭客户端
        fclose($client);
        if (is_callable($this->onClose)) {
            ($this->onClose)($this, $client);
        }
    }
}