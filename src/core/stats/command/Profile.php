<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class Profile extends PluginCommand {
    private $core;
    
    public function __construct(Core $core) {
        parent::__construct("profile", $core);
       
        $this->core = $core;
       
        $this->setPermission("core.stats.command.profile");
        $this->setUsage("[player] [global : factions : lobby]");
        $this->setDescription("Check your or a Player's Profile");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(isset($args[0])) {
			$this->core->getStats()->getCoreUser($args[0], function($user) use ($sender, $args) {
				if(is_null($user)) {
					$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
					return false;
				} else {	
					$server = "Offline";
						
					if(!is_null($user->getServer())) {
						$server = $user->getServer()->getName();
					}				
					if(!isset($args[1])) {
						$server = "Offline";
						
						if(!is_null($user->getServer())) {
							$server = $user->getServer()->getName();
						}
						$msg = $this->core->getPrefix() . $user->getName() . "'s Global Profile:\n" . TextFormat::GRAY . "Rank: " . $user->getRank()->getFormat() . "\n" . TextFormat::GRAY . "Coins: " . $this->core->getStats()->getEconomyUnit("coins") . $user->getCoins() . "\n" . TextFormat::GRAY . "Balance: " . $this->core->getStats()->getEconomyUnit("balance") . $user->getBalance() . "\n" . TextFormat::GRAY . "Server: " . $server;
					} else {
						switch(strtolower($args[1])) {
							case "global":
								$msg = $this->core->getPrefix() . $user->getName() . "'s Global Profile:\n" . TextFormat::GRAY . "Coins: " . $this->core->getStats()->getEconomyUnit("coins") . $user->getCoins() . "\n" . TextFormat::GRAY . "Balance: " . $this->core->getStats()->getEconomyUnit("balance") . $user->getBalance() . "\n" . TextFormat::GRAY . "Server: " . $server;
							break;
							case "factions":
							case "faction":
							case "fac":
								$msg = $this->core->getPrefix() . $user->getName() . "'s Factions Profile:\n" . TextFormat::GRAY . "Coming Soon!";
							break;
							case "lobby":
							case "hub":
								$msg = $this->core->getPrefix() . $user->getName() . "'s Lobby Profile:\n" . TextFormat::GRAY . "Coming Soon!";
							break;
							default:
								$msg = $this->core->getErrorPrefix() . "Type does not exist";
							break;	
						}
					}
					if(!$sender instanceof CorePlayer or isset($args[1])) {
						$sender->sendMessage($msg);
						return true;
					}
					$sender->sendProfileForm("profile", $user);
					return true;
				}
			});
			return false;
        } else if(!isset($args[0])) {
            if(!$sender instanceof CorePlayer) {
                $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
                return false;
            } else {
                $sender->sendProfileForm();
                return true;
            }
        }
        return false;
    }
}