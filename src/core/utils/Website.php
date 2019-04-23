<?php

declare(strict_types = 1);

namespace core\utils;

use pocketmine\utils\Internet;

class Website extends Internet {
    public static function splitHeader($header, $only_allow_oauth_parameters = true) {
        $pattern = '/(([-_a-z]*)=("([^"]*)"|([^,]*)),?)/';
        $offset = 0;
        $params = [];

        while(preg_match($pattern, $header, $matches, PREG_OFFSET_CAPTURE, $offset) > 0) {
            $match = $matches[0];
            $header_name = $matches[2][0];
            $header_content = (isset($matches[5])) ? $matches[5][0] : $matches[4][0];

            if(preg_match("/^oauth_/", $header_name) or !$only_allow_oauth_parameters) {
                $params[$header_name] = self::decodeRfc3986($header_content);
            }
            $offset = $match[1] + strlen($match[0]);
        }
        if(isset($params["realm"])) {
            unset($params["realm"]);
        }
        return $params;
    }

    public static function decodeRfc3986(string $string) : string {
        return urldecode($string);
    }

    public static function getHeaders() : array {
        if(function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $out = [];

            foreach($headers as $key => $value) {
                $key = str_replace(" ", "-", ucwords(strtolower(str_replace("-", " ", $key))));
                $out[$key] = $value;
            }
        } else {
            $out = [];

            if(isset($_SERVER["CONTENT_TYPE"])) {
                $out["Content-Type"] = $_SERVER["CONTENT_TYPE"];
            }
            if(isset($_ENV["CONTENT_TYPE"])) {
                $out["Content-Type"] = $_ENV["CONTENT_TYPE"];
            }
            foreach($_SERVER as $key => $value) {
                if(substr($key, 0, 5) == "HTTP_") {
                    $key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
                    $out[$key] = $value;
                }
            }
        }
        return $out;
    }

    public static function parseParameters($input) : array {
        if(!isset($input) or !$input) {
            return [];
        }
        $pairs = explode("&", $input);
        $parsed_parameters = [];

        foreach($pairs as $pair) {
            $split = explode("=", $pair, 2);
            $parameter = self::decodeRfc3986($split[0]);
            $value = isset($split[1]) ? self::decodeRfc3986($split[1]) : "";

            if(isset($parsed_parameters[$parameter])) {
                if(is_scalar($parsed_parameters[$parameter])) {
                    $parsed_parameters[$parameter] = [$parsed_parameters[$parameter]];
                }
                $parsed_parameters[$parameter][] = $value;
            } else {
                $parsed_parameters[$parameter] = $value;
            }
        }
        return $parsed_parameters;
    }

    public static function buildHTTPQuery($params) : string {
        if(!$params) {
            return "";
        }
        $keys = self::encodeRfc3986(array_keys($params));
        $values = self::encodeRfc3986(array_values($params));
        $params = array_combine($keys, $values);

        uksort($params, 'strcmp');

        $pairs = [];

        foreach($params as $parameter => $value) {
            if(is_array($value)) {
                natsort($value);

                foreach($value as $duplicate_value) {
                    $pairs[] = $parameter . "=" . $duplicate_value;
                }
            } else {
                $pairs[] = $parameter . "=" . $value;
            }
        }
        return implode("&", $pairs);
    }

    public static function encodeRfc3986($input) {
        if(is_array($input)) {
            return array_map(['self', 'encodeRfc3986'], $input);
        } else if(is_scalar($input)) {
            return str_replace("+", " ", str_replace("%7E", "~", rawurlencode(strval($input))));
        } else {
            return "";
        }
    }
}