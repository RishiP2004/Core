<?php

declare(strict_types = 1);

namespace core\network\command;

use core\Core;

use core\network\BackThread;

use pocketmine\command\{
	PluginCommand,
	CommandSender
};

class Backup extends PluginCommand {
	private $core;

	public function __construct(Core $core) {
		parent::__construct("backup", $core);

		$this->core = $core;

		$this->setPermission("core.network.backup.command");
		$this->setDescription("Backup the Server");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
		if(!$sender->hasPermission($this->getPermission())) {
			$sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
			return false;
		} else {
			$backThread = new BackThread;

			$backThread->run();
			return true;
		}
	}
}