<?php

declare(strict_types = 1);

namespace core\essential\command\defaults;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\RawStringArgument;

use core\Core;

use core\network\NetworkManager;

use core\player\traits\PlayerCallTrait;
use core\player\rank\Rank;
use core\player\PlayerManager;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class ListCommand extends BaseCommand {
	use PlayerCallTrait;

	public function prepare() : void {
		$this->setPermission("list.command");
		$this->registerArgument(0, new RawStringArgument("server", true));
	}

	public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
		$ranks = [];

		foreach(PlayerManager::getInstance()->getRanksFlat() as $rank) {
			if($rank instanceof Rank) {
				$ranks[] = $rank->getFormat();
			}
		}
		if(isset($args["server"])) {
			switch(strtolower($args["server"])) {
				case "lobby":
					$sender->sendMessage(Core::PREFIX . "Online Players in Lobby:");
						
					$lobby = NetworkManager::getInstance()->getServer("Lobby");
					$slots = $lobby->getMaxSlots();
						
					if(!empty($lobby->getOnlinePlayers())) {
						foreach($lobby->getOnlinePlayers() as $onlinePlayer) {
							$this->getCoreUser($onlinePlayer->getName(), function($onlineUser) use ($sender, $ranks, $slots) {
								$rank = $onlineUser->getRank();
								$ranks[$rank->getFormat()] = $onlineUser->getName();
									
								foreach($ranks as $r) {
									$sender->sendMessage($r[0] . TextFormat::GRAY . " (" . count((array) $r[1]) . "/" . $slots . ")" . ":" . "\n" . implode(", ", (array) $r[1]));
								}
							});
						}
					} else {
						$sender->sendMessage(TextFormat::GRAY . "No one is currently Online (0/" . $slots . ")");
					}
				break;
				case "hcf":
					$sender->sendMessage(Core::PREFIX . "Online Players in HCF:");
					    
					$factions = NetworkManager::getInstance()->getServer("HCF");
					$slots = $factions->getMaxSlots();
						
					if(!empty($factions->getOnlinePlayers())) {
						foreach($factions->getOnlinePlayers() as $onlinePlayer) {
							$this->getCoreUser($onlinePlayer->getName(), function($onlineUser) use ($sender, $ranks, $slots) {
								$rank = $onlineUser->getRank();
								$ranks[$rank->getFormat()] = $onlineUser->getName();
									
								foreach($ranks as $r) {
									$sender->sendMessage($r[0] . TextFormat::GRAY . " (" . count((array) $r[1]) . "/" . $slots . ")" . ":" . "\n" . implode(", ", (array) $r[1]));
								}
							});
						}
					} else {
						$sender->sendMessage(TextFormat::GRAY . "No one is currently Online (0/" . $slots . ")!");
					}
				break;
				default:
					$sender->sendMessage(Core::ERROR_PREFIX . "No such Server in the Athena NetworkManager");
				break;
			}
		} else {
			$sender->sendMessage(Core::PREFIX . "All Online Players:");
				
			$slots = NetworkManager::getInstance()->getTotalMaxSlots();
			
			if(!empty(NetworkManager::getInstance()->getTotalOnlinePlayers())) {
				foreach(NetworkManager::getInstance()->getTotalOnlinePlayers() as $onlinePlayer) {
					$this->getCoreUser($onlinePlayer->getName(), function($onlineUser) use ($sender, $ranks, $slots) {
						$rank = $onlineUser->getRank();
						$ranks[$rank->getFormat()] = $onlineUser->getName();
									
						foreach($ranks as $r) {
							$sender->sendMessage($r[0] . TextFormat::GRAY . " (" . count((array) $r[1]) . "/" . $slots . ")" . ":" . "\n" . implode(", ", (array) $r[1]));
						}
					});
				}
			} else {
				$sender->sendMessage(TextFormat::GRAY . "No one is currently Online (0/" . $slots . ")!");
			}
			return;
		}
	}
}
