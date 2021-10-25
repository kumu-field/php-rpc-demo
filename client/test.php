<?php

require_once __DIR__ . '/service/UserService.php';

$user_id = '123456';
$ret = UserService::instance()->getUserToken($user_id);

var_dump($ret);