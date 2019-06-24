<?php

declare(strict_types = 1);

namespace core\essentials\command;

use core\Core;

use pocketmine\command\{
	PluginCommand,
	CommandSender
};

class Hud extends PluginCommand {
	private $core;

	public function __construct(Core $core) {
		parent::__construct("hud", $core);

		$this->core = $core;

		$this->setPermission("core.essentials.hud.command");
		$this->setUsage("<type> <value>");
		$this->setDescription("Set a Hud Type on or Off");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
		if(!$sender->hasPermission($this->getPermission())) {
			$sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
			return false;
		}
		return true;
	}
}