<?php

declare(strict_types = 1);

namespace core\social;

use core\Core;

use core\utils\Manager;

use core\social\command\Discord;

use CortexP\DiscordWebHookAPI\{
	Message,
	Webhook
};
use twitter\Twitter;

class Social extends Manager implements Access {
	public static $instance = null;
	
    public function init() {
    	self::$instance = $this;
    	
		if(!empty(self::WEB_HOOK_URL)) {
			$this->registerCommand(Discord::class, new Discord($this));
		}
		if(!empty(self::KEY && self::SECRET && self::TOKEN && self::TOKEN_SECRET)) {
			$this->registerCommand(\core\social\command\Twitter::class, new \core\social\command\Twitter($this));
		}
		$this->registerListener(new SocialListener($this), Core::getInstance());
    }
    
    public static function getInstance() : self {
		return self::$instance;
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