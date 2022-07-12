<?php

declare(strict_types = 1);

namespace core\social;

use core\Core;

use core\player\CorePlayer;

use CortexPE\DiscordWebhookAPI\{
	Message,
	Webhook,
	Embed
};

use pocketmine\event\Listener;

use pocketmine\event\player\{
	PlayerChatEvent,
	PlayerJoinEvent,
	PlayerQuitEvent
};

class SocialListener implements Listener, Access {
	public function __construct(private SocialManager $manager) {}

	public function onPlayerChat(PlayerChatEvent $event) : void {
		$player = $event->getPlayer();
		$message = $event->getMessage();
		$subDiscord = strtolower(substr($message, 0, strlen(self::PREFIX["discord"])));

		if($subDiscord === self::PREFIX["discord"]) {
			if(!$player->hasPermission("core.social.discord")) {
				$player->sendMessage(Core::ERROR_PREFIX . "You cannot message to Discord");
				$event->cancel();
				return;
			}
			if(empty(self::WEB_HOOK_URL)) {
				$player->sendMessage(Core::ERROR_PREFIX . "Discord Messaging is currently Disabled");
				$event->cancel();
				return;
			}
			$msg = new Message();
			$msg->setContent($message);
			$this->manager->sendToDiscord($msg);
			$event->cancel();
			$player->sendMessage(Core::ERROR_PREFIX . "Posted Message to Discord: " . $message);
		}
	}

	public function onJoin(PlayerJoinEvent $event) : void {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			if(empty(self::WEB_HOOK_URL)) {
				return;
			}
			$webHook = new Webhook(self::JOIN_LOG);
			$msg = new Message();
			$embed = new Embed();
			$embed->setTitle("Athena | Logger");
			$embed->setColor(0x00FF00);
			$embed->setDescription($player->getName() . " has just joined the server");
			$msg->addEmbed($embed);
			$webHook->send($msg);
		}
	}

	public function onQuit(PlayerQuitEvent $event) : void {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			if(empty(self::WEB_HOOK_URL)) {
				return;
			}
			$webHook = new Webhook(self::JOIN_LOG);
			$msg = new Message();
			$embed = new Embed();
			$embed->setTitle("Athena | Logger");
			$embed->setColor(0x00FF00);
			$embed->setDescription($player->getName() . " has just left the server");
			$msg->addEmbed($embed);
			$webHook->send($msg);
		}
	}
}