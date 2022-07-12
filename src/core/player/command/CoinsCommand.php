<?php

declare(strict_types = 1);

namespace core\player\command;

use core\Core;

use core\player\CorePlayer;

use core\player\{
	Statistics,
	traits\PlayerCallTrait
};
use core\player\command\args\OfflinePlayerArgument;

use CortexPE\Commando\BaseCommand;

use pocketmine\command\{
    CommandSender
};

class CoinsCommand extends BaseCommand {
	use PlayerCallTrait;

	public function prepare() : void {
		$this->setPermission("coins.command");
		$this->registerArgument(0, new OfflinePlayerArgument("player", true));
	}
    
    public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
        if(isset($args["player"])) {
        	if(!$sender->hasPermission($this->getPermission() . ".other")) {
				$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
        		return;
			}
			$this->getCoreUser($args["PLAYER"], function($user) use ($sender, $args) {
				if(is_null($user)) {
					$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
					return false;
				} else {
					$sender->sendMessage(Core::PREFIX . $user->getName() . "'s Coins: " . Statistics::COIN_UNIT . $user->getCoins());
					return true;
				}
			});
			return;
        }
		if(!$sender instanceof CorePlayer) {
			$sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
			return;
		}
		$sender->sendMessage(Core::PREFIX . "Your Coins: " . Statistics::COIN_UNIT . $sender->getCoreUser()->getCoins());
    }
}