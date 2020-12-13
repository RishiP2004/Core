<?php

declare(strict_types = 1);

namespace core\social;

use core\Core;

use CortexP\DiscordWebHookAPI\Message;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerChatEvent;

class SocialListener implements Listener, Access {
	private $core;

	public function __construct(Core $core) {
		$this->core = $core;
	}

	public function onPlayerChat(PlayerChatEvent $event) {
		$player = $event->getPlayer();
		$message = $event->getMessage();

		$subTwitter = strtolower(substr($message, 0, strlen(self::PREFIX["twitter"])));
		$subDiscord = strtolower(substr($message, 0, strlen(self::PREFIX["discord"])));

		if($subTwitter === self::PREFIX["twitter"]) {
			if(!$player->hasPermission("core.social.twitter.tweet")) {
				$player->sendMessage($this->core->getErrorPrefix() . "You cannot Tweet to Twitter");
				$event->setCancelled();
				return;
			}
			if(empty(self::KEY && self::SECRET && self::TOKEN && self::TOKEN_SECRET)) {
				$player->sendMessage($this->core->getErrorPrefix() . "Tweeting is currently Disabled");
				$event->setCancelled();
				return;
			} else {
				$this->core->getSocial()->postTweet($message);
				$event->setCancelled();
				$player->sendMessage($this->core->getErrorPrefix() . "Posted Tweet: " . $message);
				return;
			}
		} else if($subDiscord === self::PREFIX["discord"]) {
			if(!$player->hasPermission("core.social.discord.tweet")) {
				$player->sendMessage($this->core->getErrorPrefix() . "You cannot Tweet to Twitter");
				$event->setCancelled();
				return;
			}
			if(empty(self::WEB_HOOK_URL)) {
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