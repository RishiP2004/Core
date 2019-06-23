<?php

declare(strict_types = 1);

namespace core\social\twitter;

use core\Core;

use core\social\twitter\task\TwitterSend;

use pocketmine\command\ConsoleCommandSender;

class Twitter implements Access {
    private $core;

    public function __construct(Core $core) {
        $this->core = $core;

        if(!empty($this->getKey() && $this->getSecret() && $this->getToken() && $this->getTokenSecret())) {
			$this->core->getServer()->getCommandMap()->register(\core\social\twitter\command\Twitter::class, new \core\social\twitter\command\Twitter($this->core));
		}
    }

    public function getKey() : string {
        return self::KEY;
    }

    public function getSecret() : string {
        return self::SECRET;
    }

    public function getToken() : string {
        return self::TOKEN;
    }

    public function getTokenSecret() : string {
        return self::TOKEN_SECRET;
    }

    public function postTweet(string $tweet) {
        $this->core->getServer()->getAsyncPool()->submitTask(new TwitterSend('statuses/update', null, $tweet));
    }

    public function sendDirectMessage(string $username, string $message) {
        $this->core->getServer()->getAsyncPool()->submitTask(new TwitterSend('direct_messages/new', $username, $message));
    }
	
	public function follow(string $username) {
        $this->core->getServer()->getAsyncPool()->submitTask(new TwitterSend('friendships/create', $username));
    }

    public function notifyTwitter($sender, $result) {
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
        if($result["Success"]) {
            $sender->sendMessage($this->core->getPrefix() . "Twittersuccess");
        } else {
            $this->core->getServer()->getLogger()->error($this->core->getErrorPrefix() . "Twitter wasn't Sent, Error: " . $result["Error"]);
        }
    }
}