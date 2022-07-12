<?php

declare(strict_types = 1);

namespace core\essential;

use core\Core;

use core\player\CorePlayer;

use core\utils\MathUtils;

use core\world\WorldManager;

use pocketmine\event\Listener;

use pocketmine\event\player\{
	PlayerChatEvent,
	PlayerCommandPreprocessEvent,
	PlayerPreLoginEvent
};

use pocketmine\event\server\CommandEvent;

use pocketmine\Server;

class EssentialListener implements Listener {
	private array $skipList = [];

	public function __construct(private EssentialManager $manager) {}

	public function onPlayerChat(PlayerChatEvent $event) : void {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$muteList = $this->manager->getNameMutes();
			$ipMuteList = $this->manager->getIpMutes();

			if($muteList->isBanned($player->getName())) {
				$entries = $muteList->getEntries();
				$entry = $entries[strtolower($player->getName())];
				$reason = $entry->getReason();

				if($entry->getExpires() === null) {
					if($reason !== null) {
						$muteMessage = Core::ERROR_PREFIX . "You are currently Muted for " . $reason;
					} else {
						$muteMessage = Core::ERROR_PREFIX . "You are currently Muted";
					}
				} else {
					$expiry = MathUtils::expirationTimerToString($entry->getExpires(), new \DateTime());

					if($entry->hasExpired()) {
						$muteList->remove($entry->getName());
						return;
					}
					if($reason !== null ) {
						$muteMessage = Core::ERROR_PREFIX . "You are currently Muted for " . $reason . " until " . $expiry;
					} else {
						$muteMessage = Core::ERROR_PREFIX . "You are currently Muted until " . $expiry;
					}
				}
				$event->cancel();
				$player->sendMessage($muteMessage);
			}
			if($ipMuteList->isBanned($player->getNetworkSession()->getIp())) {
				$entries = $ipMuteList->getEntries();
				$entry = $entries[strtolower($player->getNetworkSession()->getIp())];
				$reason = $entry->getReason();

				if($entry->getExpires() === null) {
					if($reason != null) {
						$muteMessage = Core::ERROR_PREFIX . "You are currently Ip Muted for " . $reason;
					} else {
						$muteMessage = Core::ERROR_PREFIX . "You are currently Ip Muted";
					}
				} else {
					$expiry = MathUtils::expirationTimerToString($entry->getExpires(), new \DateTime());

					if($entry->hasExpired()) {
						$ipMuteList->remove($entry->getName());
						return;
					}
					if($reason !== null) {
						$muteMessage = Core::ERROR_PREFIX . "You are currently Ip Muted for " . $reason . " until " . $expiry;
					} else {
						$muteMessage = Core::ERROR_PREFIX . "You are currently Ip Muted until " . $expiry;
					}
				}
				$event->cancel();
				$player->sendMessage($muteMessage);
			}
			$muted = WorldManager::getInstance()->muted;

			if(!empty($muted)) {
				$difference = array_diff(Server::getInstance()->getOnlinePlayers(), $muted);

				if(!in_array($player, $difference)) {
					$difference[] = $player;
				}
				$event->setRecipients($difference);
			}
		}
	}

	public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event) : void {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$blockList = $this->manager->getNameBlocks();
			$ipBlockList = $this->manager->getIpBlocks();

			if($blockList->isBanned($player->getName())) {
				$entries = $blockList->getEntries();
				$entry = $entries[strtolower($player->getName())];
				$reason = $entry->getReason();

				if($entry->getExpires() === null) {
					if($reason !== null) {
						$blockMessage = Core::ERROR_PREFIX . "You're currently Blocked for " . $reason;
					} else {
						$blockMessage = Core::ERROR_PREFIX . "You're currently Blocked";
					}
				} else {
					$expiry = MathUtils::expirationTimerToString($entry->getExpires(), new \DateTime());

					if($entry->hasExpired()) {
						$blockList->remove($entry->getName());
						return;
					}
					if($reason !== null) {
						$blockMessage = Core::ERROR_PREFIX . "You're currently Blocked for " . $reason . " until " . $expiry;
					} else {
						$blockMessage = Core::ERROR_PREFIX . "You're currently Blocked until " . $expiry;
					}
				}
				$event->cancel();
				$player->sendMessage($blockMessage);
			}
			if($ipBlockList->isBanned($player->getNetworkSession()->getIp())) {
				$entries = $ipBlockList->getEntries();
				$entry = $entries[strtolower($player->getNetworkSession()->getIp())];
				$reason = $entry->getReason();

				if($entry->getExpires() == null) {
					if($reason !== null) {
						$blockMessage = Core::ERROR_PREFIX . "You're currently Ip Blocked for " . $reason;
					} else {
						$blockMessage = Core::ERROR_PREFIX . "You're currently Ip Blocked";
					}
				} else {
					$expiry = MathUtils::expirationTimerToString($entry->getExpires(), new \DateTime());

					if($entry->hasExpired()) {
						$ipBlockList->remove($entry->getName());
						return;
					}
					if($reason !== null) {
						$blockMessage = Core::ERROR_PREFIX . "You're currently Ip Blocked for " . $reason . " until " . $expiry;
					} else {
						$blockMessage = Core::ERROR_PREFIX . "You're currently Ip Blocked until " . $expiry;
					}
				}
				$event->cancel();
				$player->sendMessage($blockMessage);
			}
		}
	}

	public function onPlayerPreLogin(PlayerPreLoginEvent $event) : void {
		$playerInfo = $event->getPlayerInfo();
		
		$banList = $this->manager->getNameBans();
		$ipBanList = $this->manager->getIpBans();

		if($banList->isBanned($playerInfo->getUsername())) {
			$entries = $banList->getEntries();
			$entry = $entries[strtolower($playerInfo->getUsername())];

			if($entry->getExpires() === null) {
				$reason = $entry->getReason();

				if($reason !== null or $reason !== "") {
					$banMessage = Core::ERROR_PREFIX . "You are currently Banned for " . $reason;
				} else {
					$banMessage = Core::ERROR_PREFIX . "You are currently Banned";
				}
			} else {
				$expiry = MathUtils::expirationTimerToString($entry->getExpires(), new \DateTime());
					
				if($entry->hasExpired()) {
					$banList->remove($entry->getName());
					return;
				}
				$banReason = $entry->getReason();

				if($banReason !== null) {
					$banMessage = Core::ERROR_PREFIX . "You are currently Banned for " . $banReason . " until " . $expiry;
				} else {
					$banMessage = Core::ERROR_PREFIX . "You are currently Banned until " . $expiry;
				}
			}
			$event->setKickReason($event::KICK_REASON_BANNED, $banMessage);
		}
		if($ipBanList->isBanned($playerInfo->getUsername())) {
			$entries = $ipBanList->getEntries();
			$entry = $entries[strtolower($playerInfo->getUsername())];

			if($entry->getExpires() === null) {
				$reason = $entry->getReason();

				if($reason !== null or $reason !== "") {
					$banMessage = Core::ERROR_PREFIX . "You are currently Ip Banned for " . $reason;
				} else {
					$banMessage = Core::ERROR_PREFIX . "You are currently Ip Banned";
				}
			} else {
				$expiry = MathUtils::expirationTimerToString($entry->getExpires(), new \DateTime());

				if($entry->hasExpired()) {
					$ipBanList->remove($entry->getName());
					return;
				}
				$banReason = $entry->getReason();

				if($banReason !== null) {
					$banMessage = Core::ERROR_PREFIX . "You are currently Ip Banned for " . $banReason . " until " . $expiry;
				} else {
					$banMessage = Core::ERROR_PREFIX . "You are currently Ip Banned until " . $expiry;
				}
			}
			$event->setKickReason($event::KICK_REASON_BANNED, $banMessage);
		}
	}
	//Thanks Kim.
	public function onCommandEvent(CommandEvent $event) : void {
        $args = explode(" ", rtrim($event->getCommand(), "\r\n"));
        $label = array_shift($args);

        if(isset($this->skipList[$label])) {
            return;
        }
        if(isset($this->replaceMap[$label])) {
            $event->setCommand(implode(" ", [$this->replaceMap[$label], ...$args]));
            return;
        }
        $knownCommands = Server::getInstance()->getCommandMap()->getCommands();
		
        if(isset($knownCommands[$label])) {
            $this->skipList[$label] = true;
            return;
        }
        foreach($knownCommands as $key => $value){
            if(strcasecmp($label, $find = $key) === 0 or strcasecmp($label, $find = $value->getLabel()) === 0) {
                $this->replaceMap[$label] = $find;
                $event->setCommand(implode(" ", [$find, ...$args]));
                return;
            }
        }
        $this->skipList[$label] = true;
    }
}