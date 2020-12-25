<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use core\stats\{
	Stats,
	Statistics
};

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class Profile extends PluginCommand {
    private $manager;
    
    public function __construct(Stats $manager) {
        parent::__construct("profile", Core::getInstance());
       
        $this->manager = $manager;
       
        $this->setPermission("core.stats.command.profile");
        $this->setUsage("[player : simple or s] [global : factions : lobby]");
        $this->setDescription("Check your or a Player's Profile");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(isset($args[0])) {
			if(strtolower($args[0]) === "simple" or strtolower($args[0]) === "s") {
				if(!isset($args[1])) {
					$msg = Core::PREFIX . "Your Global Profile:\n" . TextFormat::GRAY . "Rank: " . $sender->getCoreUser()->getRank()->getFormat() . "\n" . TextFormat::GRAY . "Coins: " . Statistics::UNITS["coins"] . $sender->getCoreUser()->getCoins() . "\n" . TextFormat::GRAY . "Balance: " . Statistics::UNITS["balance"] . $sender->getCoreUser()->getBalance() . "\n" . TextFormat::GRAY . "Server: " . $sender->getCoreUser()->getServer()->getName();
				} else {
					switch(strtolower($args[1])) {
						case "global":
						break;
						case "factions":
						case "faction":
						case "fac":
							$msg = Core::PREFIX . "Your Factions Profile:\n" . TextFormat::GRAY . "Coming Soon!";
						break;
						case "lobby":
						case "hub":
							$msg = Core::PREFIX . "Your Lobby Profile:\n" . TextFormat::GRAY . "Coming Soon!";
						break;
						default:
							$msg = Core::ERROR_PREFIX . "Type does not exist";
						break;	
					}
				}
				$sender->sendMessage($msg);
				return true;
			}
			$this->manager->getCoreUser($args[0], function($user) use ($sender, $args) {
				if(is_null($user)) {
					$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
					return false;
				} else {	
					$server = "Offline";
					$msg = "";
						
					if(!is_null($user->getServer())) {
						$server = $user->getServer()->getName();
					}				
					if(!isset($args[1])) {
						$server = "Offline";
						
						if(!is_null($user->getServer())) {
							$server = $user->getServer()->getName();
						}
						$msg = Core::PREFIX . $user->getName() . "'s Global Profile:\n" . TextFormat::GRAY . "Rank: " . $user->getRank()->getFormat() . "\n" . TextFormat::GRAY . "Coins: " . Statistics::UNITS["coins"]. $user->getCoins() . "\n" . TextFormat::GRAY . "Balance: " . Statistics::UNITS["balance"] . $user->getBalance() . "\n" . TextFormat::GRAY . "Server: " . $server;
					} else {
						switch(strtolower($args[1])) {
							case "global":
							break;
							case "factions":
							case "faction":
							case "fac":
								$msg = Core::PREFIX . $user->getName() . "'s Factions Profile:\n" . TextFormat::GRAY . "Coming Soon!";
							break;
							case "lobby":
							case "hub":
								$msg = Core::PREFIX . $user->getName() . "'s Lobby Profile:\n" . TextFormat::GRAY . "Coming Soon!";
							break;
							default:
								$msg = Core::ERROR_PREFIX . "Profile Type does not exist";
							break;	
						}
					}
					if(!$sender instanceof CorePlayer or isset($args[1])) {
						$sender->sendMessage($msg);
						return true;
					}
					$sender->sendProfileForm("profile", $user);
					$sender->sendMessage(Core::PREFIX . "Opened " . $user->getName() . "'s Profile menu");
					return true;
				}
			});
			return false;
        } else if(!isset($args[0])) {
            if(!$sender instanceof CorePlayer) {
                $sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
                return false;
            } else {
                $sender->sendProfileForm();
				$sender->sendMessage(Core::PREFIX . "Opened Profile menu");
                return true;
            }
        }
        return false;
    }
}