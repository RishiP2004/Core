<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;

use core\essentials\Essentials;

use core\stats\Stats;

use core\network\Network;

use core\stats\rank\Rank;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};
use pocketmine\utils\TextFormat;

class Lists extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
		parent::__construct("list", Core::getInstance());

        $this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.list");
        $this->setUsage("[server]");
        $this->setDescription("See all Online Players of a Server or the Network");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        } else {
            $ranks = [];

            foreach(Stats::getInstance()->getRanks() as $rank) {
                if($rank instanceof Rank) {
                    $ranks[] = $rank->getFormat();
                }
            }
            if(isset($args[0])) {
				switch(strtolower($args[0])) {
					case "lobby":
						$sender->sendMessage(Core::PREFIX . "Online Players in Lobby:");
						
						$lobby = Network::getInstance()->getServer("Lobby");
						$slots = $lobby->getMaxSlots();
						
						if(!empty($lobby->getOnlinePlayers())) {
							foreach($lobby->getOnlinePlayers() as $onlinePlayer) {								
								Stats::getInstance()->getCoreUser($onlinePlayer->getName(), function($onlineUser) use ($sender, $ranks, $slots) {
									$rank = $onlineUser->getRank();
									$ranks[$rank->getFormat()] = $onlineUser->getName();
									
									foreach($ranks as $r) {
										$sender->sendMessage((string) $r[0] . TextFormat::GRAY . " (" . count((array) $r[1]) . "/" . $slots . ")" . ":" . "\n" . implode(", ", (array) $r[1]));
									}
								});
							}
						} else {
							$sender->sendMessage(TextFormat::GRAY . "No one is currently Online (0/" . $slots . ")");
						}
					break;
					case "factions":
					    $sender->sendMessage(Core::PREFIX . "Online Players in Survival:");
					    
						$factions = Network::getInstance()->getServer("Survival");
						$slots = $factions->getMaxSlots();
						
						if(!empty($factions->getOnlinePlayers())) {
							foreach($factions->getOnlinePlayers() as $onlinePlayer) {
								Stats::getInstance()->getCoreUser($onlinePlayer->getName(), function($onlineUser) use ($sender, $ranks, $slots) {
									$rank = $onlineUser->getRank();
									$ranks[$rank->getFormat()] = $onlineUser->getName();
									
									foreach($ranks as $r) {
										$sender->sendMessage((string) $r[0] . TextFormat::GRAY . " (" . count((array) $r[1]) . "/" . $slots . ")" . ":" . "\n" . implode(", ", (array) $r[1]));
									}
								});
							}
						} else {
							$sender->sendMessage(TextFormat::GRAY . "No one is currently Online (0/" . $slots . ")!");
						}
					break;
					default:
						$sender->sendMessage(Core::ERROR_PREFIX . "No such Server in the Athena Network");
					break;
				}
			} else {
				$sender->sendMessage(Core::PREFIX . "All Online Players:");
				
				$slots = Network::getInstance()->getTotalMaxSlots();
			
				if(!empty(Network::getInstance()->getTotalOnlinePlayers())) {
					foreach(Network::getInstance()->getTotalOnlinePlayers() as $onlinePlayer) {
						Stats::getInstance()->getCoreUser($onlinePlayer->getName(), function($onlineUser) use ($sender, $ranks, $slots) {
							$rank = $onlineUser->getRank();
							$ranks[$rank->getFormat()] = $onlineUser->getName();
									
							foreach($ranks as $r) {
								$sender->sendMessage((string) $r[0] . TextFormat::GRAY . " (" . count((array) $r[1]) . "/" . $slots . ")" . ":" . "\n" . implode(", ", (array) $r[1]));
							}
						});
					}
				} else {
					$sender->sendMessage(TextFormat::GRAY . "No one is currently Online (0/" . $slots . ")!");
				}
				return true;
			}
			return false;
        }
    }
}
