<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../../interfaces/IUser.php';
require_once __DIR__ . '/../client/RpcClient.php';

class UserService extends BaseService implements IUser
{
    private $client;

    public function __construct() {
        $this->client = RpcClient::instance()->service(IUser::SERVICE);
    }

    /**
     * @param $user_id
     * @return mixed
     */
    public function getUserToken($user_id) {
        return $this->client->getUserToken($user_id);
    }
}
