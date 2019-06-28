<?php

declare(strict_types = 1);

namespace core\essentials\command;

use core\Core;

use pocketmine\command\{
	PluginCommand,
	CommandSender
};

class Chat extends PluginCommand {
	private $core;

	public function __construct(Core $core) {
		parent::__construct("chat", $core);

		$this->core = $core;

		$this->setPermission("core.essentials.chat.command");
		$this->setUsage("<type> [player]");
		$this->setDescription("Change Chat Type of a Player or yourself");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
		if(!$sender->hasPermission($this->getPermission())) {
			$sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
			return false;
		}
		return true;
	}
}