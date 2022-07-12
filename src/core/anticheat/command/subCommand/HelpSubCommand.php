<?php

declare(strict_types = 1);

namespace core\anticheat\command\subCommand;

use core\Core;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class HelpSubCommand extends BaseSubCommand {
	public function prepare() : void {
		$this->setPermission("cheat.subcommand.help");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$message = [
			Core::PREFIX . "Cheats HelpSubCommand:",
			TextFormat::GRAY . "/cheat help",
			TextFormat::GRAY . "/cheat report <player> <cheat>",
			TextFormat::GRAY . "/cheat history <add : remove : set> <player> <cheat> <amount>",
		];
		foreach($message as $line) {
			$sender->sendMessage($line);
		}
    }
}