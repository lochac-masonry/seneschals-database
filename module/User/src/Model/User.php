<?php

namespace User\Model;

class User
{
    public $username;
    public $hashedPassword;

    public function exchangeArray(array $data)
    {
        $this->username = !empty($data['username']) ? $data['username'] : null;
        $this->hashedPassword = !empty($data['hashed_password']) ? $data['hashed_password'] : null;
    }
}
