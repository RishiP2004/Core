<?php

declare(strict_types = 1);

namespace core\broadcast\command;

use core\Core;

use core\broadcast\command\subCommand\{
    HelpSubCommand,
    SendMessageSubCommand,
    SendPopupSubCommand,
    SendTitleSubCommand
};

use CortexPE\Commando\BaseCommand;

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class BroadcastCommand extends BaseCommand {
	public function prepare() : void {
		$this->registerSubCommand(new HelpSubCommand("help", "", ["h, ?"]));
		$this->registerSubCommand(new SendMessageSubCommand("sendmessage", "Send a message", ["sm"]));
		$this->registerSubCommand(new SendPopupSubCommand("sendpopup", "Send a popup", ["sp"]));
		$this->registerSubCommand(new SendTitleSubCommand("sendtitle", "Send a title", ["st"]));
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