<?php

namespace core\social\twitter\oAuth;

class Consumer {
    protected $key = "", $secret = "", $callbackURL = "";

    public function __construct($key, $secret, $callbackURL = null) {
        $this->key = $key;
        $this->secret = $secret;
        $this->callbackURL = $callbackURL;
    }

    public function getKey() : string {
        return $this->key;
    }

    public function getSecret() : string {
        return $this->secret;
    }

    public function getCallbackUrl() : string {
        return $this->callbackURL;
    }

    public function __toString() : string {
        return "Consumer [key = " . $this->key . ", secret = " . $this->secret . "]";
    }
}