<?php

declare(strict_types = 1);

namespace core\anticheat\command;

use core\anticheat\AntiCheat;
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
	private $manager;

	private $subCommands = [], $commandObjects = [];

	public function __construct(AntiCheat $manager) {
		parent::__construct("cheat", Core::getInstance());

		$this->manager = $manager;

		$this->setAliases(["hack"]);
		$this->setPermission("core.chat.command");
		$this->setDescription("Cheat Command");
		$this->loadSubCommand(new Help($manager));
		$this->loadSubCommand(new Report($manager));
		$this->loadSubCommand(new History($manager));
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
			$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
			return false;
		}
		if(!isset($args[0])) {
			$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /cheat help");
			return false;
		}
		$subCommand = array_shift($args);

		if(!isset($this->subCommands[$subCommand])) {
			$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /cheat help");
			return false;
		}
		$command = $this->commandObjects[$this->subCommands[$subCommand]];

		if(!$command->canUse($sender)) {
			$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
		} else {
			if(!$command->execute($sender, $args)) {
				$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /cheat " . $command->getName() . " " . $command->getUsage());
				return false;
			} else {
				return true;
			}
		}
		return false;
	}
}