<?php

require_once __DIR__ . '/RpcServer.php';
require_once __DIR__ . '/RpcService.php';
require_once __DIR__ . '/RpcProtocol.php';

// 配置 socket 服务启动信息
$server = RpcServer::instance([
    'host'  => '127.0.0.1',
    'port'  => 6969
]);

// 配置 rpc 微服务目录
RpcService::config([
    'path'  => __DIR__ . '/api/services'
]);

// rpc 调用回调
$server->onCall = function ($socket, $client) {
    // 获取头部信息
    $header = $socket->receive($client, RpcProtocol::HEADER_SIZE);
    // 解析头部并验证合法性
    $header = RpcProtocol::parseHeader($header);
    if (!RpcProtocol::validRequest($header['sign'] ?? '')) {
        echo "invalid request\r\n";
        goto close;
    }
    // 获取内容
    $body = $socket->receive($client, intval($header['body_len']));
    if (!empty($body)) {
        echo 'receive body:' . $body . "\r\n";
        // 解析获取获得要调用的类、方法、及参数
        RpcProtocol::decode($body, $class, $method, $params);
        // 执行方法
        $result = RpcService::execMethod($class, $method, $params);
        // 打包结果数据
        $data = RpcProtocol::pack($result);
        //  结果数据返回给调用方
        $socket->send($client, $data);
    }

close:
    $socket->close($client);
};

$server->onClose = function ($socket, $client) {
    echo "close connection\r\n";
};

$server->run();