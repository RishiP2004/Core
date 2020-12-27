<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use core\stats\{
	Stats,
	Statistics
};

use pocketmine\Server;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class TakeCoins extends PluginCommand {
    private $manager;

    public function __construct(Stats $manager) {
        parent::__construct("takecoins", Core::getInstance());

        $this->manager = $manager;

        $this->setPermission("core.stats.command.takecoins");
        $this->setUsage("<player> <amount>");
        $this->setDescription("Take Coins from a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /takecoins " . $this->getUsage());
            return false;
        }
		if(!is_numeric($args[1])) {
            $sender->sendMessage(Core::ERROR_PREFIX . $args[1] . " is not a valid Number");
            return false;
        }
        if(is_float($args[1])) {
            $sender->sendMessage(Core::ERROR_PREFIX . $args[1] . " must be an Integer");
            return false;
        }
		$this->manager->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
				return false;
			}
			if($user->getCoins() - $args[1] < 0) {
				$sender->sendMessage(Core::ERROR_PREFIX . $user->getName() . " will have less than 0 Coins");
				return false;
			} else {
				$user->setCoins($user->getCoins() - (int) $args[1]);

				$player = Server::getInstance()->getPlayer($user->getName());
		
				if($player instanceof CorePlayer) {
					$player->sendMessage(Core::PREFIX . $sender->getName() . " Took " . Statistics::COIN_UNIT . $args[1] . " from you");
				}
				$sender->sendMessage(Core::PREFIX . "Took away " . Statistics::COIN_UNIT . $args[1] . " from " . $user->getName());
				return true;
			}
        });
		return false;
    }
}