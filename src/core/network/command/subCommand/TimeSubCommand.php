<?php

declare(strict_types = 1);

namespace core\network\command\subCommand;

use CortexPE\Commando\BaseSubCommand;

use core\Core;

use core\network\NetworkManager;

use core\utils\MathUtils;

use pocketmine\command\CommandSender;

class TimeSubCommand extends BaseSubCommand {
	public function prepare() : void {
		$this->setPermission("restarter.subcommand.time");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$sender->sendMessage(Core::PREFIX . "Time remaining until restart: " . MathUtils::getFormattedTime(NetworkManager::getInstance()->getTimer()->getTime()));
	}
}