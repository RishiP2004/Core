<?php

namespace core\social\twitter\oAuth;

use core\utils\Website;

class TwitterOAuth extends OAuth {
    public function __construct(string $consumerKey, string $consumerSecret, string $oauthToken, string $oauthTokenSecret) {
        $this->setSignatureMethod(new SignatureMethod());
        $this->setConsumer(new Consumer($consumerKey, $consumerSecret));

        if(!empty($oauthToken) && !empty($oauthTokenSecret)) {
            $this->setToken(new Token($oauthToken, $oauthTokenSecret));
        } else {
            $this->setToken(null);
        }
    }

    public function getRequestToken(string $oauthCallback) : array {
        $parameters = [];
        $parameters["oauth_callback"] = $oauthCallback;
        $request = $this->oAuthRequest($this->requestTokenURL(), "GET", $parameters);
        $token = Website::parseParameters($request);

        $this->setToken(new Token($token["oauth_token"], $token["oauth_token_secret"]));
        return $token;
    }

    public function oAuthRequest(string $url, string $method, array $parameters) {
        if(strrpos($url, "https://") !== 0 && strrpos($url, "http://") !== 0) {
            $url = "{ " . $this->getHost() . "}{" . $url . "}.json";
        }
        $consumerAndToken = Request::fromConsumerAndToken($this->getConsumer(), $this->getToken(), $method, $url, $parameters);

        $consumerAndToken->signRequest($this->getSignatureMethod(), $this->getConsumer(), $this->getToken());

        switch($method) {
            case "GET":
                return $this->http($consumerAndToken->toUrl(), "GET");
            break;
            default:
                return $this->http($consumerAndToken->getNormalizedHTTPUrl(), $method, $consumerAndToken->toPostData());
            break;
        }
    }

    public function http(string $url, string $method, $postFields = null) {
        $ci = curl_init();

        curl_setopt($ci, CURLOPT_USERAGENT, $this->getUserAgent());
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->getConnectionTimeout());
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->getTimeout());
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_HTTPHEADER, ['Expect:']);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_HEADER, false);

        switch($method) {
            case "POST":
                curl_setopt($ci, CURLOPT_POST, true);

                if(!empty($postFields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postFields);
                }
            break;
            case "DELETE":
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, "DELETE");

                if(!empty($postFields)) {
                    $url = "{$url}?{$postFields}";
                }
            break;
        }
        curl_setopt($ci, CURLOPT_URL, $url);

        $response = curl_exec($ci);

        curl_getinfo($ci, CURLINFO_HTTP_CODE);
        array_merge([], curl_getinfo($ci));
        curl_close($ci);
        return $response;
    }

    public function getAuthorizeURL(string $token, bool $signIn = true) : string {
        if(is_array($token)) {
            $token = $token["oath_token"];
        }
        if(empty($signIn)) {
            return $this->authorizeURL() . "?oauth_token = {" . $token . "}";
        } else {
            return $this->authenticateURL() . "?oauth_token = { . " . $token . "}";
        }
    }

    public function getAccessToken(string $oauthVerifier) : array {
        $parameters = [];
        $parameters["oath_verifier"] = $oauthVerifier;
        $request = $this->oAuthRequest($this->accessTokenURL(), "GET", $parameters);
        $token = Website::parseParameters($request);

        $this->setToken(new Token($token["oauth_token"], $token["oauth_token_secret"]));
        return $token;
    }

    public function getXAuthToken(string $username, string $password) {
        $parameters = [];
        $parameters["x_auth_username"] = $username;
        $parameters["x_auth_password"] = $password;
        $parameters["x_auth_mode"] = "client_auth";
        $request = $this->oAuthRequest($this->accessTokenURL(), "POST", $parameters);
        $token = Website::parseParameters($request);

        $this->setToken(new Token($token["oath_token"], $token["oauth_token_secret"]));
        return $token;
    }

    public function get(string $url, array $parameters = []) {
        $response = $this->oAuthRequest($url, "GET", $parameters);
        return json_decode($response);
    }

    public function post(string $url, array $parameters = []) {
        $response = $this->oAuthRequest($url, "POST", $parameters);
        return json_decode($response);
    }

    public function delete(string $url, array $parameters = []) {
        $response = $this->oAuthRequest($url, "DELETE", $parameters);
        return json_decode($response);
    }
}