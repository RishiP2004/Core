<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;

use core\stats\rank\Rank;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};
use pocketmine\utils\TextFormat;

class Lists extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("list", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.list.command");
        $this->setUsage("[server]");
        $this->setDescription("See all Online Players of a Server or the Network");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        } else {
            $ranks = [];

            foreach($this->core->getStats()->getRanks() as $rank) {
                if($rank instanceof Rank) {
                    $ranks[] = $rank->getFormat();
                }
            }
            if(isset($args[0])) {
				switch(strtolower($args[0])) {
					case "lobby":
						$sender->sendMessage($this->core->getPrefix() . "Online Players in Lobby:");
						
						$lobby = $this->core->getNetwork()->getServer("Lobby");
						$slots = $lobby->getMaxSlots();
						
						if(!empty($lobby->getOnlinePlayers())) {
							foreach($lobby->getOnlinePlayers() as $onlinePlayer) {								
								$this->core->getStats()->getCoreUser($onlinePlayer->getName(), function($onlineUser) use ($sender, $ranks, $slots) {          
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
					    $sender->sendMessage($this->core->getPrefix() . "Online Players in Factions:");
					    
						$factions = $this->core->getNetwork()->getServer("Factions");
						$slots = $factions->getMaxSlots();
						
						if(!empty($factions->getOnlinePlayers())) {
							foreach($factions->getOnlinePlayers() as $onlinePlayer) {
								$this->core->getStats()->getCoreUser($onlinePlayer->getName(), function($onlineUser) use ($sender, $ranks, $slots) {
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
						$sender->sendMessage($this->core->getErrorPrefix() . "No such Server in the Athena Network");
					break;
				}
			} else {
				$sender->sendMessage($this->core->getPrefix() . "All Online Players:");
				
				$slots = $this->core->getNetwork()->getTotalMaxSlots();
			
				if(!empty($this->core->getNetwork()->getTotalOnlinePlayers())) {
					foreach($this->core->getNetwork()->getTotalOnlinePlayers() as $onlinePlayer) {
						$this->core->getStats()->getCoreUser($onlinePlayer->getName(), function($onlineUser) use ($sender, $ranks, $slots) {
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
