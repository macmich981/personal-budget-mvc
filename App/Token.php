<?php

namespace App;

class Token {

    protected $token;

    public function __construct($token_value = null) {
        if ($token_value) {
            $this->token = $token->value;
        } else {
            $this->token = bin2hex(random_bytes(16));
        }
    }

    public function getValue() {
        return $this->token;
    }

    public function getHash() {
        return hash_hmac('sha25', $this->token, \App\Config::SECRET_KEY);
    }

}