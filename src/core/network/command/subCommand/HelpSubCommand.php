<?php

declare(strict_types = 1);

namespace core\network\command\subCommand;

use core\Core;

use CortexPE\Commando\BaseSubCommand;

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class HelpSubCommand extends BaseSubCommand {
	public function prepare() : void {
		$this->setPermission("restarter.subcommand.help");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
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