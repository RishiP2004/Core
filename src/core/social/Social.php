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

		if(!empty(self::WEB_HOOK_URL)) {
			$this->core->getServer()->getCommandMap()->register(Discord::class, new Discord($core));
		}
		if(!empty(self::KEY && self::SECRET && self::TOKEN && self::TOKEN_SECRET)) {
			$this->core->getServer()->getCommandMap()->register(\core\social\command\Twitter::class, new \core\social\command\Twitter($this->core));
		}
    }

	public function sendToDiscord(Message $message) {
    	if(!empty($this))
		$webHook = new Webhook(self::WEB_HOOK_URL);

		$webHook->send($message);
	}

	public function postTweet(string $tweet) {
		$twitter = new Twitter(self::KEY, self::SECRET, self::TOKEN, self::TOKEN_SECRET);

		$twitter->postTweet($tweet);
	}

	public function twitterDirectMessage(string $username, string $message) {
		$twitter = new Twitter(self::KEY, self::SECRET, self::TOKEN, self::TOKEN_SECRET);

		$twitter->sendDirectMessage($username, $message);
	}

	public function twitterFollow(string $username) {
		$twitter = new Twitter(self::KEY, self::SECRET, self::TOKEN, self::TOKEN_SECRET);

		$twitter->follow($username);
	}
}