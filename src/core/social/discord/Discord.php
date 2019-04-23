<?php

declare(strict_types = 1);

namespace core\social\discord;

use core\Core;

use core\social\discord\task\DiscordSend;

use pocketmine\command\ConsoleCommandSender;

class Discord implements Access {
    private $core;

    public function __construct(Core $core) {
        $this->core = $core;

        $this->core->getServer()->getCommandMap()->register(\core\social\discord\command\Discord::class, new \core\social\discord\command\Discord($core));
    }

    public function getWebHookURL() : string {
        return self::WEB_HOOK_URL;
    }

    public function getChatURL() : string {
        return self::CHAT_URL;
    }

    public function getChatUsername() : string {
        return self::CHAT_USERNAME;
    }

    public function getChatFormat() : string {
        return self::CHAT_FORMAT;
    }

    public function getPrefix() : string {
        return self::PREFIX;
    }

    public function getUsername() : string {
        return self::USERNAME;
    }

    public function sendMessageToDiscord(string $webHook, string $message, string $sender = null, string $username = null) {
        if(!isset($username)) {
            $username = $this->getUsername();
        }
        $curlOPTS = [
            "content" => $message,
            "username" => $username
        ];

        $this->core->getServer()->getAsyncPool()->submitTask(new DiscordSend($sender, $webHook, serialize($curlOPTS)));
    }

    public function notifyDiscord($sender, $result) {
        if($sender === "nolog") {
            return;
        } else if($sender === "CONSOLE") {
            $sender = new ConsoleCommandSender();
        } else {
            $senderInstance = $this->core->getServer()->getPlayerExact($sender);

            if($senderInstance === null) {
                return;
            } else {
                $sender = $senderInstance;
            }
        }
        if($result["success"]) {
            $sender->sendMessage($this->core->getPrefix() . "Discord message was sent");
        } else {
            $this->core->getServer()->getLogger()->error($this->core->getErrorPrefix() . "Discord message wasn't Sent, Error: " . $result["error"]);
        }
    }
}