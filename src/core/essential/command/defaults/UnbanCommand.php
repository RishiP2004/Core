<?php

declare(strict_types = 1);

namespace core\essential\command\defaults;

use CortexPE\Commando\BaseCommand;

use core\Core;

use core\player\CorePlayer;
use core\player\traits\PlayerCallTrait;
use core\player\command\args\OfflinePlayerArgument;

use core\essential\EssentialManager;

use pocketmine\Server;

use pocketmine\command\CommandSender;

class UnbanCommand extends BaseCommand {
	use PlayerCallTrait;

	public function prepare() : void {
		$this->setPermission("unban.command");
		$this->registerArgument(0, new OfflinePlayerArgument("player"));
	}

	public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
		$this->getCoreUser($args["player"], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args["player"] . " is not a valid Player");
				return false;
			}
			$banList = EssentialManager::getInstance()->getNameBans();

			if(!$banList->isBanned($user->getName())) {
				$sender->sendMessage(Core::ERROR_PREFIX . $user->getName() . " is not Banned");
				return false;
			} else {
				$banList->remove($user->getName());

				$player = Server::getInstance()->getPlayerByPrefix($user->getName());

				if($player instanceof CorePlayer) {
					$player->sendMessage(Core::PREFIX . "You have been Unbanned By: " . $sender->getName());
				}
				$sender->sendMessage(Core::PREFIX . "You have Unbanned " . $user->getName());
				Server::getInstance()->broadcastMessage(Core::PREFIX . $user->getName() . " has been Unbanned by " . $sender->getName());
				return true;
			}
        });
		return;
    }
}
