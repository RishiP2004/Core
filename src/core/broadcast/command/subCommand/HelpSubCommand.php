<?php

declare(strict_types = 1);

namespace core\broadcast\command\subCommand;

use core\Core;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class HelpSubCommand extends BaseSubCommand {
	public function prepare() : void {
		$this->setPermission("broadcast.subcommand.help");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$message = [
			Core::PREFIX . "Broadcast HelpCommand:",
			TextFormat::GRAY . "/broadcast help",
			TextFormat::GRAY . "/broadcast sendmessage <message>",
			TextFormat::GRAY . "/broadcast sendpopup <popup>",
			TextFormat::GRAY . "/broadcast sendtitle <title>"
		];
		foreach($message as $line) {
			$sender->sendMessage($line);
		}
	}
}