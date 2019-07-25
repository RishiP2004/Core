<?php

declare(strict_types = 1);

namespace core\social;

use core\Core;

use discord\Message;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerChatEvent;

class SocialListener implements Listener {
	private $core;

	public function __construct(Core $core) {
		$this->core = $core;
	}

	public function onPlayerChat(PlayerChatEvent $event) {
		$player = $event->getPlayer();
		$message = $event->getMessage();

		$subTwitter = strtolower(substr($message, 0, strlen($this->core->getSocial()->getPrefix("twitter"))));
		$subDiscord = strtolower(substr($message, 0, strlen($this->core->getSocial()->getPrefix("discord"))));

		if($subTwitter === $this->core->getSocial()->getPrefix("twitter")) {
			if(!$player->hasPermission("core.social.twitter.tweet")) {
				$player->sendMessage($this->core->getErrorPrefix() . "You cannot Tweet to Twitter");
				$event->setCancelled();
				return;
			}
			if(empty($this->core->getSocial()->getKey() && $this->core->getSocial()->getSecret() && $this->core->getSocial()->getToken() && $this->core->getSocial()->getTokenSecret())) {
				$player->sendMessage($this->core->getErrorPrefix() . "Tweeting is currently Disabled");
				$event->setCancelled();
				return;
			} else {
				$this->core->getSocial()->postTweet($message);
				$event->setCancelled();
				$player->sendMessage($this->core->getErrorPrefix() . "Posted Tweet: " . $message);
				return;
			}
		} else if($subDiscord === $this->core->getSocial()->getPrefix("discord")) {
			if(!$player->hasPermission("core.social.twitter.tweet")) {
				$player->sendMessage($this->core->getErrorPrefix() . "You cannot Tweet to Twitter");
				$event->setCancelled();
				return;
			}
			if(empty($this->core->getSocial()->getWebHookURL())) {
				$player->sendMessage($this->core->getErrorPrefix() . "Discord Messaging is currently Disabled");
				$event->setCancelled();
				return;
			} else {
				$msg = new Message();

				$msg->setContent($message);
				$this->core->getSocial()->sendToDiscord($msg);
				$event->setCancelled();
				$player->sendMessage($this->core->getErrorPrefix() . "Posted Message to Discord: " . $message);
				return;
			}
		}
	}
}