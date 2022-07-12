<?php

declare(strict_types = 1);

namespace core\anticheat\command;

use core\Core;

use CortexPE\Commando\BaseCommand;

use core\anticheat\command\subCommand\{
	HelpSubCommand,
	ReportSubCommand,
	HistorySubCommand
};

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class CheatCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("cheat.command");
		$this->registerSubCommand(new HelpSubCommand("help", "See available commands", ["h, ?"]));
		$this->registerSubCommand(new ReportSubCommand("report", "Report a player", []));
		$this->registerSubCommand(new HistorySubCommand("history", "Cheat History of a Player", []));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$message = [
			Core::PREFIX . "Cheats Help:",
			TextFormat::GRAY . "/cheat help",
			TextFormat::GRAY . "/cheat report <player> <cheat>",
			TextFormat::GRAY . "/cheat history <add : remove : set> <player> <cheat> <amount>",
		];
		foreach($message as $line) {
			$sender->sendMessage($line);
		}
	}
}