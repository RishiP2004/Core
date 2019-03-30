<?php

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
                    $ranks[0] = $rank;
                }
            }
            if(isset($args[0])) {
				switch(strtolower($args[0])) {
					case "lobby":
						$sender->sendMessage($this->core->getPrefix() . "Online Players in Lobby:");
						
                        foreach($this->core->getNetwork()->getServer("Lobby")->getOnlinePlayers() as $onlinePlayer) {
                            $onlineUser = $this->core->getStats()->getCoreUser($onlinePlayer);

                            $rank = $onlineUser->getRank();

                            if(in_array($rank, $ranks)) {
                                $ranks[1] = $onlineUser->getName();
                                $ranks[2]++;
                            }
                        }
                        foreach($ranks as $r) {
                            $sender->sendMessage($r->getFormat() . TextFormat::GRAY . " (" . $r[2] . ")" . ":" . "\n" . implode(", ", $r[1]));
                        }
					break;
					case "factions":
					    $sender->sendMessage($this->core->getPrefix() . "Online Players in Factions:");
					    
					    foreach($this->core->getNetwork()->getServer("Factions")->getOnlinePlayers() as $onlinePlayer) {
                            $onlineUser = $this->core->getStats()->getCoreUser($onlinePlayer);

                            $rank = $onlineUser->getRank();

                            if(in_array($rank, $ranks)) {
                                $ranks[1] = $onlineUser->getName();
                                $ranks[2]++;
                            }
                        }
                        foreach($ranks as $r) {
                            $sender->sendMessage($r->getFormat() . TextFormat::GRAY . " (" . $r[2] . ")" . ":" . "\n" . implode(", ", $r[1]));
                        }
					break;
					default:
						$sender->sendMessage($this->core->getErrorPrefix() . "No such Server in the Athena Network");
					break;
				}
			}
			$sender->sendMessage($this->core->getPrefix() . "All Online Players:");
            
            foreach($this->core->getNetwork()->getTotalOnlinePlayers() as $onlinePlayer) {
                $onlineUser = $this->core->getStats()->getCoreUser($onlinePlayer);

                $rank = $onlineUser->getRank();
                
                if(in_array($rank, $ranks)) {
                    $ranks[1] = $onlineUser->getName();
                    $ranks[2]++;
                }
            }
            foreach($ranks as $r) {
                $sender->sendMessage($r->getFormat() . TextFormat::GRAY . " (" . $r[2] . ")" . ":" . "\n" . implode(", ", $r[1]));
            }
            return true;
        }
    }
}