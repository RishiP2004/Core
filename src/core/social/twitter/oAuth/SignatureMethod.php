<?php

namespace core\social\twitter\oAuth;

use core\utils\Website;

class SignatureMethod {
    public function buildSignature(Request $request, Consumer $consumer, Token $token) : string {
        $baseString = $request->getSignatureBaseString();
        $request->baseString = $baseString;
        $key_parts = [
            $consumer->getSecret(),
            ($token) ? $token->getSecret() : ""
        ];
        $key_parts = Website::encodeRfc3986($key_parts);
        $key = implode("&", $key_parts);
        return base64_encode(hash_hmac("shal", $baseString, $key, true));
    }
}