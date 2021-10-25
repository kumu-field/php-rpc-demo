<?php

interface IUser
{
    const SERVICE = 'User';
    
    public function getUserToken($user_id);
}