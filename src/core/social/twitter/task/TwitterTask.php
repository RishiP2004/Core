<?php

namespace core\social\twitter\task;

use core\Core;

use core\social\twitter\Access;

use core\social\twitter\oAuth\TwitterOAuth;

use pocketmine\scheduler\AsyncTask;

class TwitterTask extends AsyncTask implements Access {
    private $url = "", $message = "";
    /**
     * @var string|null
     */
    private $username = "";
	
	public function __construct(string $url, string $username, string $message = "") {
        $this->url = $url;
        $this->username = $username;
        $this->message = $message;
    }

    public function onRun() : void {
        $twitter = new TwitterOAuth(self::KEY, self::SECRET, self::TOKEN, self::TOKEN_SECRET);

		switch($this->url) {
			case "statuses/update":
				$twitter->post($this->url, ["status" => $this->message]);
			break;
			case "direct_messages/new":
				$twitter->post($this->url, ["screen_name" => $this->username, "text" => $this->message]);
			break;
			case "friendships/create":
				$twitter->post($this->url, ["screen_name" => $this->username]);
			break;
		}
    }

    public function onCompletion() : void {
        Core::getInstance()->getSocial()->getTwitter()->notifyTwitter($this->username, $this->getResult());
    }
}