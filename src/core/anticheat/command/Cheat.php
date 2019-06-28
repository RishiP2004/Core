<?php

declare(strict_types = 1);

namespace core\anticheat\command;

use core\Core;

use core\utils\SubCommand;

use core\anticheat\command\subCommand\{
	Help,
	Report,
	History
};

use pocketmine\command\{
	PluginCommand,
	CommandSender
};

class Cheat extends PluginCommand {
	private $core;

	private $subCommands = [], $commandObjects = [];

	public function __construct(Core $core) {
		parent::__construct("cheat", $core);

		$this->core = $core;

		$this->setAliases(["hack"]);
		$this->setPermission("core.broadcast.command");
		$this->setDescription("Broadcast Command");
		$this->loadSubCommand(new Help($core));
		$this->loadSubCommand(new Report($core));
		$this->loadSubCommand(new History($core));
	}

	private function loadSubCommand(SubCommand $subCommand) {
		$this->commandObjects[] = $subCommand;
		$commandId = count($this->commandObjects) - 1;
		$this->subCommands[$subCommand->getName()] = $commandId;

		foreach($subCommand->getAliases() as $alias) {
			$this->subCommands[$alias] = $commandId;
		}
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
		if(!$sender->hasPermission($this->getPermission())) {
			$sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
			return false;
		}
		if(!isset($args[0])) {
			$sender->sendMessage($this->core->getErrorPrefix() . "Usage: /cheat help");
			return false;
		}
		$subCommand = array_shift($args);

		if(!isset($this->subCommands[$subCommand])) {
			$sender->sendMessage($this->core->getErrorPrefix() . "Usage: /cheat help");
			return false;
		}
		$command = $this->commandObjects[$this->subCommands[$subCommand]];

		if(!$command->canUse($sender)) {
			$sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
		} else {
			if(!$command->execute($sender, $args)) {
				$sender->sendMessage($this->core->getErrorPrefix() . "Usage: /cheat" . " " . $command->getName() . " " . $command->getUsage());
				return false;
			} else {
				return true;
			}
		}
		return false;
	}
}