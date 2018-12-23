<?php

namespace core\social\twitter\oAuth;

class OAuth {
    protected $consumer;

    protected $token;

    protected $signatureMethod;

    protected $host = "https://api.twitter.com/1.1/", $userAgent = "TwitterOAuth v0.2.0-beta2";

    protected $timeout = 30, $connectTimeout = 30;
	
    public function getConsumer() : Consumer {
        return $this->consumer;
    }

    public function setConsumer(Consumer $consumer) {
        $this->consumer = $consumer;
    }

    public function getToken() : Token {
        return $this->token;
    }

    public function setToken(Token $token) {
        $this->token = $token;
    }

    public function getSignatureMethod() : SignatureMethod {
        return $this->signatureMethod;
    }

    public function setSignatureMethod(SignatureMethod $signatureMethod) {
        $this->signatureMethod = $signatureMethod;
    }

    public function getHost() : string {
        return $this->host;
    }

    public function setHost(string $host) {
        $this->host = $host;
    }

    public function getTimeout() : int {
        return $this->timeout;
    }

    public function setTimeout(int $time) {
        $this->timeout = $time;
    }

    public function setConnectionTimeout(int $time) {
        $this->connectTimeout = $time;
    }

    public function getConnectionTimeout() : int {
        return $this->connectTimeout;
    }

    public function setUserAgent(string $agent) {
        $this->userAgent = $agent;
    }

    public function getUserAgent() : string {
        return $this->userAgent;
    }

    public function accessTokenURL() : string {
        return "https://api.twitter.com/oAuth/access_token";
    }

    public function authenticateURL() : string {
        return "https://api.twitter.com/oAuth/authenticate";
    }

    public function authorizeURL() : string {
        return "https://api.twitter.com/oAuth/authorize";
    }

    public function requestTokenURL() : string {
        return "https://api.twitter.com/oAuth/request_token";
    }
}