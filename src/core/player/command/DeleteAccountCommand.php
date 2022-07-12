<?php

declare(strict_types = 1);

namespace core\player\command;

use core\Core;

use core\player\CorePlayer;

use core\player\PlayerManager;
use core\player\traits\PlayerCallTrait;
use core\player\command\args\OfflinePlayerArgument;

use CortexPE\Commando\BaseCommand;

use pocketmine\Server;

use pocketmine\command\CommandSender;

class DeleteAccountCommand extends BaseCommand {
	use PlayerCallTrait;

	public function prepare() : void {
		$this->setPermission("deleteaccount.command");
		$this->registerArgument(0, new OfflinePlayerArgument("player", false));
	}

    public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
		$this->getCoreUser($args["player"]->getName(), function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
				return false;
			} else {
				PlayerManager::getInstance()->unregisterCoreUser($user);
				unlink(Server::getInstance()->getDataPath() . "players/" . strtolower($user->getName()) . ".dat");
				
				$player = Server::getInstance()->getPlayerByPrefix($user->getName());
		
				if($player instanceof CorePlayer) {
					$player->kick($sender->getName() . " deleted your Account");
				}
				$sender->sendMessage(Core::PREFIX . "Deleted " . $user->getName() . "'s Account");
				return true;
			}
        });
    }
}