<?php

declare(strict_types = 1);

namespace core\social\twitter\oAuth;

use core\utils\Website;

class Request {
    public $baseString;

    private $parameters = [];

    private $httpMethod, $httpURL;

    public function __construct($httpMethod, $httpURL, $parameters = null) {
        @$parameters or $parameters = [];
        $parameters = array_merge(Website::parseParameters(parse_url($httpURL, PHP_URL_QUERY)), $parameters);
        $this->parameters = $parameters;
        $this->httpMethod = $httpMethod;
        $this->httpURL = $httpURL;
    }

    public static function fromConsumerAndToken(Consumer $consumer, Token $token, $httpMethod, $httpUrl, $parameters = null) {
        @$parameters or $parameters = [];
        $defaults = [
            "oauth_version" => "1.0",
            "oauth_nonce" => md5(microtime() . mt_rand()),
            "oauth_timestamp" => time(),
            "oauth_consumer_key" => $consumer->getKey()
        ];

        if($token) {
            $defaults["oauth_token"] = $token->getKey();
        }
        $parameters = array_merge($defaults, $parameters);
        return new Request($httpMethod, $httpUrl, $parameters);
    }

    public function fromRequest($httpMethod = null, $httpUrl = null, $parameters = null) : Request {
        $scheme = (!isset($_SERVER["HTTPS"]) or $_SERVER["HTTPS"] !== "on") ? "http" : "https";
        @$httpUrl or $httpUrl = $scheme . "://" . $_SERVER["HTTP_HOST"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        @$httpMethod or $httpMethod = $_SERVER["REQUEST_METHOD"];

        if(!$parameters) {
            $requestHeaders = Website::getHeaders();
            $parameters = Website::parseParameters($_SERVER["QUERY_STRING"]);

            if($httpMethod === "POST" && @strstr($requestHeaders["Content-Type"], "application/x-www-form-urlencoded")) {
                $postData = Website::parseParameters(file_get_contents("php://input"));
                $parameters = array_merge($parameters, $postData);
            }
            if(@substr($requestHeaders["Authorization"], 0, 6) === "oAuth ") {
                $headerParameters = Website::splitHeader($requestHeaders["Authorization"]);
                $parameters = array_merge($parameters, $headerParameters);
            }
        }
        return new Request($httpMethod, $httpUrl, $parameters);
    }

    public function getParameter($name) {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }

    public function getParameters() : array {
        return $this->parameters;
    }

    public function unsetParameter($name) {
        unset($this->parameters[$name]);
    }

    public function getSignatureBaseString() : string {
        $parts = [
            $this->getNormalizedHTTPMethod(),
            $this->getNormalizedHTTPUrl(),
            $this->getSignableParameters()
        ];
        $parts = Website::encodeRfc3986($parts);
        return implode("&", $parts);
    }

    public function getNormalizedHTTPMethod() : string {
        return strtoupper($this->httpMethod);
    }

    public function getNormalizedHTTPURL() : string {
        $parts = parse_url($this->httpURL);
        $port = @$parts["port"];
        $scheme = $parts["scheme"];
        $host = $parts["host"];
        $path = @$parts["path"];
        $port or $port = ($scheme === "https") ? "443" : "80";

        if(($scheme === "https" && $port !== "443") or ($scheme === "http" && $port !== 80)) {
            $host = $host . ":" . $port;
        }
        return $scheme . "://" . $host . $path;
    }

    public function getSignableParameters() {
        $params = $this->parameters;

        if(isset($params["oauth_signature"])) {
            unset($params["oauth_signature"]);
        }
        return Website::buildHTTPQuery($params);
    }

    public function toHeader($realm = null) : string {
        $first = true;

        if($realm) {
            $out = 'Authorization: oAuth realm = "' . Website::encodeRfc3986($realm) . '"';
            $first = false;
        } else {
            $out = "Authorization: oAuth";
        }
        foreach($this->parameters as $k => $v) {
            if(substr($k, 0, 5) !== "oAuth") {
                if(is_array($v)) {
                    throw new \Exception("Arrays are not supported in headers");
                }
                $out .= ($first) ? " " : ",";
                $out .= Website::encodeRfc3986($k) . '="' . Website::encodeRfc3986($v) . '"';
                $first = false;
            }
        }
        return $out;
    }

    public function signRequest(SignatureMethod $signatureMethod, Consumer $consumer, Token $token) {
        $this->setParameter("oauth_signature_method", "HMAC-SHA1", false);

        $signature = $this->buildSignature($signatureMethod, $consumer, $token);

        $this->setParameter("oauth_signature", $signature, false);
    }

    public function setParameter($name, $value, $allowDuplicates = true) {
        if($allowDuplicates && isset($this->parameters[$name])) {
            if(is_scalar($this->parameters[$name])) {
                $this->parameters[$name] = [$this->parameters[$name]];
            }
            $this->parameters[$name][] = $value;
        } else {
            $this->parameters[$name] = $value;
        }
    }

    public function buildSignature(SignatureMethod $signatureMethod, Consumer $consumer, Token $token) : string {
        $signature = $signatureMethod->buildSignature($this, $consumer, $token);
        return $signature;
    }

    public function toString() : string {
        return $this->toUrl();
    }

    public function toUrl() : string {
        $postData = $this->toPostData();
        $out = $this->getNormalizedHTTPUrl();

        if($postData) {
            $out .= "?" . $postData;
        }
        return $out;
    }

    public function toPostData() : string {
        return Website::buildHTTPQuery($this->parameters);
    }
}