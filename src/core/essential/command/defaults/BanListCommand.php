<?php

declare(strict_types = 1);

namespace core\essential\command\defaults;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\RawStringArgument;

use core\Core;

use core\essential\EssentialManager;
use core\essential\permission\BanEntry;

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class BanListCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("banlist.command");
		$this->registerArgument(0, new RawStringArgument("type"));
	}

	public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
		switch(strtolower($args["type"])) {
			case "players":
				$list = EssentialManager::getInstance()->getNameBans()->getEntries();
				$message = implode(", ", array_map(function(BanEntry $entry) {
					return $entry->getName();
				}, $list));

				$sender->sendMessage(Core::PREFIX . "Banned Players (x" . count($list)  . "):");
				$sender->sendMessage(TextFormat::GRAY . $message);
			break;
			case "ips":
				$list = EssentialManager::getInstance()->getIpBans()->getEntries();
				$message = implode(", ", array_map(function(BanEntry $entry) {
					return $entry->getName();
				}, $list));

				$sender->sendMessage(Core::PREFIX . "Banned Ips (x" . count($list)  . "):");
				$sender->sendMessage(TextFormat::GRAY . $message);
			break;
        }
        return;
    }
}