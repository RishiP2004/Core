<?php

namespace core\social\twitter\oAuth;

use core\utils\Website;

class Token {
    protected $key, $secret;

    public function __construct($key, $secret) {
        $this->key = $key;
        $this->secret = $secret;
    }

    public function getKey() {
        return $this->key;
    }

    public function getSecret() {
        return $this->secret;
    }

    public function __toString() : string {
        return "oauth_token = " . Website::encodeRfc3986($this->getKey()) . "&oauth_token_secret = " . Website::encodeRfc3986($this->getSecret());
    }
}