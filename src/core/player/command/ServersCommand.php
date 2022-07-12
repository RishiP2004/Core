<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;

use core\player\CorePlayer;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\{
    CommandSender
};

class ServersCommand extends BaseCommand {
	public function prepare() : void {
		$this->addConstraint(new InGameRequiredConstraint());
		$this->setPermission("servers.command");
	}

	public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
		if(!$sender->hasPermission($this->getPermission())) {
			$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
			return;
		}
		if(!$sender instanceof CorePlayer) {
			$sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
			return;
		}
		//TODO
		//$sender->sendServerSelectorForm();
		$sender->sendMessage(Core::PREFIX . "Opened Servers menu");
	}
}