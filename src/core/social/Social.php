<?php

declare(strict_types = 1);

namespace core\social;

use core\Core;

use core\social\command\Discord;

use discord\{
	Message,
	Webhook
};
use twitter\Twitter;

class Social implements Access {
    private $core;

    public function __construct(Core $core) {
        $this->core = $core;

		if(!empty($this->getWebHookURL())) {
			$this->core->getServer()->getCommandMap()->register(Discord::class, new Discord($core));
		}
		if(!empty($this->getKey() && $this->getSecret() && $this->getToken() && $this->getTokenSecret())) {
			$this->core->getServer()->getCommandMap()->register(\core\social\command\Twitter::class, new \core\social\command\Twitter($this->core));
		}
    }

	public function getWebHookURL() : string {
		return self::WEB_HOOK_URL;
	}

	public function getUsername() : string {
    	return self::USERNAME;
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

	public function getPrefix(string $type) : string {
		return self::PREFIX[$type];
	}

	public function sendToDiscord(Message $message) {
    	if(!empty($this))
		$webHook = new Webhook($this->getWebHookURL());

		$webHook->send($message);
	}

	public function postTweet(string $tweet) {
		$twitter = new Twitter($this->getKey(), $this->getSecret(), $this->getToken(), $this->getTokenSecret());

		$twitter->postTweet($tweet);
	}

	public function twitterDirectMessage(string $username, string $message) {
		$twitter = new Twitter($this->getKey(), $this->getSecret(), $this->getToken(), $this->getTokenSecret());

		$twitter->sendDirectMessage($username, $message);
	}

	public function twitterFollow(string $username) {
		$twitter = new Twitter($this->getKey(), $this->getSecret(), $this->getToken(), $this->getTokenSecret());

		$twitter->follow($username);
	}
}