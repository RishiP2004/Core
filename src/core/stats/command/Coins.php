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

class Coins extends PluginCommand {
    private $manager;
    
    public function __construct(Stats $manager) {
        parent::__construct("coins", Core::getInstance());
       
        $this->manager = $manager;
       
        $this->setPermission("core.stats.command.coins");
        $this->setUsage("[player]");
        $this->setDescription("Check your or a Player's Coins");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(isset($args[0])) {
        	if(!$sender->hasPermission($this->getPermission() . ".other")) {
				$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
        		return false;
			}
			$this->manager->getCoreUser($args[0], function($user) use ($sender, $args) {
				if(is_null($user)) {
					$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
					return false;
				} else {
					$sender->sendMessage(Core::PREFIX . $user->getName() . "'s Coins: " . Statistics::COIN_UNIT . $user->getCoins());
					return true;
				}
			});
			return false;
        } else if(!isset($args[0])) {
            if(!$sender instanceof CorePlayer) {
                $sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
                return false;
            } else {
				$sender->sendMessage(Core::PREFIX . "Your Coins: " . Statistics::COIN_UNIT . $sender->getCoreUser()->getCoins());
                return true;
            }
        }
        return false;
    }
}