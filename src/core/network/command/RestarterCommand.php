<?php

declare(strict_types = 1);

namespace core\network\command;

use core\Core;

use CortexPE\Commando\BaseCommand;

use core\network\command\subCommand\{
    AddSubCommand,
    HelpSubCommand,
    MemorySubCommand,
    SetSubCommand,
    StartSubCommand,
    StopSubCommand,
    SubtractSubCommand,
    TimeSubCommand
};

use pocketmine\utils\TextFormat;

use pocketmine\command\CommandSender;

class RestarterCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("restarter.command");
		$this->registerSubCommand(new AddSubCommand("add", "Add to restart timer", []));
		$this->registerSubCommand(new HelpSubCommand("help", "Restarter Help", []));
		$this->registerSubCommand(new MemorySubCommand("memory", "Check Server Memory", []));
		$this->registerSubCommand(new SetSubCommand("set", "Set restarter timer", []));
		$this->registerSubCommand(new StartSubCommand("start", "Start restart timer", []));
		$this->registerSubCommand(new StopSubCommand("stop", "Stop restart timer", []));
		$this->registerSubCommand(new SubtractSubCommand("subtract", "Subtract from restart timer", []));
		$this->registerSubCommand(new TimeSubCommand("time", "Check time for server restart", []));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		/**foreach($this->getSubCommands() as $subCommand) {
			$subCommand->onRun($sender, $aliasUsed, $args);
		}*/
		$message = [
			Core::PREFIX . "Restarter Help:",
			TextFormat::GRAY . "/restarter help",
			TextFormat::GRAY . "/restarter add <time>",
			TextFormat::GRAY . "/restarter memory",
			TextFormat::GRAY . "/restarter set <time>",
			TextFormat::GRAY . "/restarter start",
			TextFormat::GRAY . "/restarter stop",
			TextFormat::GRAY . "/restarter subtract <time>",
			TextFormat::GRAY . "/restarter time",
		];
		foreach($message as $line) {
			$sender->sendMessage($line);
		}
	}
}