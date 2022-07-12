<?php

declare(strict_types = 1);

namespace core\player\command;

use core\Core;

use core\player\command\args\OfflinePlayerArgument;
use core\player\CorePlayer;
use core\player\traits\PlayerCallTrait;

use CortexPE\Commando\BaseCommand;

use pocketmine\command\CommandSender;

class RankCommand extends BaseCommand {
	use PlayerCallTrait;

	public function prepare() : void {
		$this->setPermission("rank.command");
		$this->registerArgument(0, new OfflinePlayerArgument("player", true));
	}
    
    public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
        if(isset($args["player"])) {
        	if(!$sender->hasPermission($this->getPermission() . ".other")) {
				$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
        		return;
			}
			$this->getCoreUser($args["player"], function($user) use ($sender, $args) {
				if(is_null($user)) {
					$sender->sendMessage(Core::ERROR_PREFIX . $args["player"] . " is not a valid Player");
					return false;
				} else {
					$sender->sendMessage(Core::PREFIX . $user->getName() . "'s RankCommand: " . $user->getRank()->getFormat());
					return true;
				}
			});
			return;
        }
		if(!$sender instanceof CorePlayer) {
			$sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
			return;
		}
		$sender->sendMessage(Core::PREFIX . "Your Rank: " . $sender->getCoreUser()->getRank()->getFormat());
    }
}