<?php

declare(strict_types = 1);

namespace core\network\command\subCommand;

use core\Core;

use core\network\NetworkManager;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseSubCommand;

use pocketmine\command\CommandSender;

class AddSubCommand extends BaseSubCommand {
	public function prepare() : void {
		$this->setPermission("restarter.subcommand.add");
		$this->registerArgument(0, new IntegerArgument("time"));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		NetworkManager::getInstance()->getTimer()->addTime((int) $args[0]);
		$sender->sendMessage(Core::ERROR_PREFIX . "Added " . $args[0] . " seconds to Restart timer");
	}
}