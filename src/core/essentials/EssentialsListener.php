<?php

declare(strict_types = 1);

namespace core\essentials;

use core\Core;
use core\CorePlayer;

use core\utils\Math;

use pocketmine\event\Listener;

use pocketmine\event\player\{
	PlayerChatEvent,
	PlayerCommandPreprocessEvent,
	PlayerPreLoginEvent
};

class EssentialsListener implements Listener {
	private $core;

	public function __construct(Core $core) {
		$this->core = $core;
	}

	public function onPlayerChat(PlayerChatEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$muteList = $this->core->getEssentials()->getNameMutes();
			$ipMuteList = $this->core->getEssentials()->getIpMutes();

			if($muteList->isBanned($player->getName())) {
				$entries = $muteList->getEntries();
				$entry = $entries[strtolower($player->getName())];
				$reason = $entry->getReason();

				if($entry->getExpires() === null) {
					if($reason !== null or $reason !== "") {
						$muteMessage = $this->core->getErrorPrefix() . "You are currently Muted for " . $reason;
					} else {
						$muteMessage = $this->core->getErrorPrefix() . "You are currently Muted";
					}
				} else {
					$expiry = Math::expirationTimerToString($entry->getExpires(), new \DateTime());

					if($entry->hasExpired()) {
						$muteList->remove($entry->getName());
						return;
					}
					if($reason !== null or $reason !== "") {
						$muteMessage = $this->core->getErrorPrefix() . "You are currently Muted for " . $reason . " until " . $expiry;
					} else {
						$muteMessage = $this->core->getErrorPrefix() . "You are currently Muted until " . $expiry;
					}
				}
				$event->setCancelled(true);
				$player->sendMessage($muteMessage);
			}
			if($ipMuteList->isBanned($player->getAddress())) {
				$entries = $ipMuteList->getEntries();
				$entry = $entries[strtolower($player->getAddress())];
				$reason = $entry->getReason();

				if($entry->getExpires() === null) {
					if($reason != null or $reason != "") {
						$muteMessage = $this->core->getErrorPrefix() . "You are currently Ip Muted for " . $reason;
					} else {
						$muteMessage = $this->core->getErrorPrefix() . "You are currently Ip Muted";
					}
				} else {
					$expiry = Math::expirationTimerToString($entry->getExpires(), new \DateTime());

					if($entry->hasExpired()) {
						$ipMuteList->remove($entry->getName());
						return;
					}
					if($reason !== null or $reason !== "") {
						$muteMessage = $this->core->getErrorPrefix() . "You are currently Ip Muted for " . $reason . " until " . $expiry;
					} else {
						$muteMessage = $this->core->getErrorPrefix() . "You are currently Ip Muted until " . $expiry;
					}
				}
				$event->setCancelled(true);
				$player->sendMessage($muteMessage);
			}
			$muted = $this->core->getWorld()->muted;

			if(!empty($muted)) {
				$difference = array_diff($this->core->getServer()->getOnlinePlayers(), $muted);

				if(!in_array($player, $difference)) {
					$difference[] = $player;
				}
				$event->setRecipients($difference);
			}
		}
	}

	public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$blockList = $this->core->getEssentials()->getNameBlocks();
			$ipBlockList = $this->core->getEssentials()->getIpBlocks();

			if($blockList->isBanned($player->getName())) {
				$entries = $blockList->getEntries();
				$entry = $entries[strtolower($player->getName())];
				$reason = $entry->getReason();

				if($entry->getExpires() === null) {
					if($reason !== null or $reason !== "") {
						$blockMessage = $this->core->getErrorPrefix() . "You're currently Blocked for " . $reason;
					} else {
						$blockMessage = $this->core->getErrorPrefix() . "You're currently Blocked";
					}
				} else {
					$expiry = Math::expirationTimerToString($entry->getExpires(), new \DateTime());

					if($entry->hasExpired()) {
						$blockList->remove($entry->getName());
						return;
					}
					if($reason !== null or $reason !== "") {
						$blockMessage = $this->core->getErrorPrefix() . "You're currently Blocked for " . $reason . " until " . $expiry;
					} else {
						$blockMessage = $this->core->getErrorPrefix() . "You're currently Blocked until " . $expiry;
					}
				}
				$event->setCancelled(true);
				$player->sendMessage($blockMessage);
			}
			if($ipBlockList->isBanned($player->getAddress())) {
				$entries = $ipBlockList->getEntries();
				$entry = $entries[strtolower($player->getAddress())];
				$reason = $entry->getReason();

				if($entry->getExpires() == null) {
					if($reason !== null or $reason !== "") {
						$blockMessage = $this->core->getErrorPrefix() . "You're currently Ip Blocked for " . $reason;
					} else {
						$blockMessage = $this->core->getErrorPrefix() . "You're currently Ip Blocked";
					}
				} else {
					$expiry = Math::expirationTimerToString($entry->getExpires(), new \DateTime());

					if($entry->hasExpired()) {
						$ipBlockList->remove($entry->getName());
						return;
					}
					if($reason !== null or $reason !== "") {
						$blockMessage = $this->core->getErrorPrefix() . "You're currently Ip Blocked for " . $reason . " until " . $expiry;
					} else {
						$blockMessage = $this->core->getErrorPrefix() . "You're currently Ip Blocked until " . $expiry;
					}
				}
				$event->setCancelled(true);
				$player->sendMessage($blockMessage);
			}
		}
	}

	public function onPlayerPreLogin(PlayerPreLoginEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$banList = $this->core->getEssentials()->getNameBans();
			$ipBanList = $this->core->getEssentials()->getIpBans();

			if($banList->isBanned($player->getName())) {
				$entries = $banList->getEntries();
				$entry = $entries[strtolower($player->getName())];

				if($entry->getExpires() === null) {
					$reason = $entry->getReason();

					if($reason !== null or $reason !== "") {
						$banMessage = $this->core->getErrorPrefix() . "You are currently Banned for " . $reason;
					} else {
						$banMessage = $this->core->getErrorPrefix() . "You are currently Banned";
					}
				} else {
					$expiry = Math::expirationTimerToString($entry->getExpires(), new \DateTime());

					if($entry->hasExpired()) {
						$banList->remove($entry->getName());
						return;
					}
					$banReason = $entry->getReason();

					if($banReason !== null || $banReason !== "") {
						$banMessage = $this->core->getErrorPrefix() . "You are currently Banned for " . $banReason . " until " . $expiry;
					} else {
						$banMessage = $this->core->getErrorPrefix() . "You are currently Banned until " . $expiry;
					}
				}
				$event->setCancelled(true);
				$player->sendMessage($banMessage);
			}
			if($ipBanList->isBanned($player->getName())) {
				$entries = $ipBanList->getEntries();
				$entry = $entries[strtolower($player->getName())];

				if($entry->getExpires() === null) {
					$reason = $entry->getReason();

					if($reason !== null or $reason !== "") {
						$banMessage = $this->core->getErrorPrefix() . "You are currently Ip Banned for " . $reason;
					} else {
						$banMessage = $this->core->getErrorPrefix() . "You are currently Ip Banned";
					}
				} else {
					$expiry = Math::expirationTimerToString($entry->getExpires(), new \DateTime());

					if($entry->hasExpired()) {
						$ipBanList->remove($entry->getName());
						return;
					}
					$banReason = $entry->getReason();

					if($banReason !== null || $banReason !== "") {
						$banMessage = $this->core->getErrorPrefix() . "You are currently Ip Banned for " . $banReason . " until " . $expiry;
					} else {
						$banMessage = $this->core->getErrorPrefix() . "You are currently Ip Banned until " . $expiry;
					}
				}
				$event->setCancelled(true);
				$player->sendMessage($banMessage);
			}
		}
	}
}