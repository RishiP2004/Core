<?php

declare(strict_types = 1);

namespace core\social\discord\task;

use core\Core;

use pocketmine\Server;

use pocketmine\scheduler\AsyncTask;

class DiscordSend extends AsyncTask {
    private $sender, $curlOPTS;
    
    private $webHook = "";
    
    public function __construct($sender, string $webHook, $curlOPTS) {
        $this->sender = $sender;
        $this->webHook = $webHook;
        $this->curlOPTS = $curlOPTS;
    }
    
    public function onRun() : void {
        $curl = curl_init();
       
        curl_setopt($curl, CURLOPT_URL, $this->webHook);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(unserialize($this->curlOPTS)));
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $responseJson = json_decode($response, true);
        $success = false;
        $error = "error";
        
        if($curlError !== "") {
            $error = $curlError;
        } else if(curl_getinfo($curl, CURLINFO_HTTP_CODE) !== 204) {
            $error = $responseJson["message"];
        } else if(curl_getinfo($curl, CURLINFO_HTTP_CODE) === 204 OR $response === "") {
            $success = true;
        }
        $result = ["response" => $response, "error" => $error, "success" => $success];
        
        $this->setResult($result);
    }
    
    public function onCompletion(Server $server) : void {
        Core::getInstance()->getSocial()->getDiscord()->notifyDiscord($this->sender, $this->getResult());
    }
}