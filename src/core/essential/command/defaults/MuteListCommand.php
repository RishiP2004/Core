<?php

declare(strict_types = 1);

namespace core\essential\command\defaults;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\RawStringArgument;

use core\Core;

use core\essential\EssentialManager;
use core\essential\permission\MuteEntry;

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class MuteListCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("mutelist.command");
		$this->registerArgument(0, new RawStringArgument("type", false));
	}

    public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
        switch(strtolower($args["type"])) {
        	case "players":
        		$list = EssentialManager::getInstance()->getNameMutes()->getEntries();
        		$message = implode(", ", array_map(function(MuteEntry $entry) {
        			return $entry->getName();
        		}, $list));

        		$sender->sendMessage(Core::PREFIX . "Muted Players (x" . count($list)  . "):");
        		$sender->sendMessage(TextFormat::GRAY . $message);
            break;
            case "ips":
            	$list = EssentialManager::getInstance()->getIpMutes()->getEntries();
            	$message = implode(", ", array_map(function(MuteEntry $entry) {
            		return $entry->getName();
            	}, $list));

            	$sender->sendMessage(Core::PREFIX . "Muted IPs (x" . count($list)  . "):");
            	$sender->sendMessage(TextFormat::GRAY . $message);
            break;
        }
	}
}