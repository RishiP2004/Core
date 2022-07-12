<?php

declare(strict_types = 1);

namespace core\network\command\subCommand;

use core\Core;

use core\network\NetworkManager;

use CortexPE\Commando\BaseSubCommand;

use pocketmine\command\CommandSender;

class StopSubCommand extends BaseSubCommand {
	public function prepare() : void {
		$this->setPermission("restarter.subcommand.stop");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		if(NetworkManager::getInstance()->getTimer()->isPaused()) {
			$sender->sendMessage(Core::ERROR_PREFIX . "Timer is already paused");
			return;
		}
		NetworkManager::getInstance()->getTimer()->setPaused(true);
		$sender->sendMessage(Core::PREFIX . "Restart Timer is paused");
		return;
	}
}