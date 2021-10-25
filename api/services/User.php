<?php

require_once __DIR__ . '/../../interfaces/IUser.php';
require_once __DIR__ . '/../components/Response.php';
require_once __DIR__ . '/../components/Message.php';

class User implements IUser {

    /**
     * @param $user_id
     */
    public function getUserToken($user_id) {
        if (!$user_id) {
            return Response::error(Message::USER_ID_EMPTY);
        }
        $data = [
            'user_id' => $user_id,
            'access_token' => 'test-access_token',
            'expires_in' => 100
        ];
        return Response::success($data);
    }
}