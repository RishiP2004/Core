<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\player\CorePlayer;
use core\player\command\args\OfflinePlayerArgument;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;

use core\stats\{
	PlayerManager,
	Statistics
};

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class ProfileCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("givecoins.command");
		$this->registerArgument(0, new OfflinePlayerArgument("player : simple", true));
		//$this->registerArgument(1, new RawStringArgument("server", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		//TODO
	}/**
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(isset($args[0])) {
			if(strtolower($args[0]) === "simple" or strtolower($args[0]) === "s") {
				if(!isset($args[1])) {
					$msg = Core::PREFIX . "Your Global ProfileCommand:\n" . TextFormat::GRAY . "RankCommand: " . $sender->getCoreUser()->getRank()->getFormat() . "\n" . TextFormat::GRAY . "Coins: " . Statistics::COIN_UNIT . $sender->getCoreUser()->getCoins() . $sender->getCoreUser()->getBalance() . "\n" . TextFormat::GRAY . "Server: " . $sender->getCoreUser()->getServer()->getName();
				} else {
					switch(strtolower($args[1])) {
						case "global":
						break;
						case "survival":
						case "surv":
							$msg = Core::PREFIX . "Your HCF ProfileCommand:\n" . TextFormat::GRAY . "Coming Soon!";
						break;
						case "lobby":
						case "hub":
							$msg = Core::PREFIX . "Your Lobby ProfileCommand:\n" . TextFormat::GRAY . "Coming Soon!";
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
				if(!$sender->hasPermission($this->getPermission() . ".other")) {
					$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
					return false;
				}
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
						$msg = Core::PREFIX . $user->getName() . "'s Global ProfileCommand:\n" . TextFormat::GRAY . "RankCommand: " . $user->getRank()->getFormat() . "\n" . TextFormat::GRAY . "Coins: " . Statistics::COIN_UNIT . $user->getCoins() . "\n" . TextFormat::GRAY . "Server: " . $server;
					} else {
						switch(strtolower($args[1])) {
							case "global":
							break;
							case "hcf":
								$msg = Core::PREFIX . $user->getName() . "'s HCF ProfileCommand:\n" . TextFormat::GRAY . "Coming Soon!";
							break;
							case "lobby":
							case "hub":
								$msg = Core::PREFIX . $user->getName() . "'s Lobby ProfileCommand:\n" . TextFormat::GRAY . "Coming Soon!";
							break;
							default:
								$msg = Core::ERROR_PREFIX . "ProfileCommand Type does not exist";
							break;	
						}
					}
					if(!$sender instanceof CorePlayer or isset($args[1])) {
						$sender->sendMessage($msg);
						return true;
					}
					$sender->sendProfileForm("profile", $user);
					$sender->sendMessage(Core::PREFIX . "Opened " . $user->getName() . "'s ProfileCommand menu");
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
				$sender->sendMessage(Core::PREFIX . "Opened ProfileCommand menu");
                return true;
            }
        }
        return false;
    }*/
}