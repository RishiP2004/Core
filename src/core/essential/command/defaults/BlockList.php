<?php

declare(strict_types = 1);

namespace core\essential\command\defaults;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\RawStringArgument;

use core\Core;

use core\essential\EssentialManager;
use core\essential\permission\BlockEntry;

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class BlockList extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("blocklist.command");
		$this->registerArgument(0, new RawStringArgument("type", false));
	}

	public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
        switch(strtolower($args["type"])) {
        	case "players":
        		$list = EssentialManager::getInstance()->getNameBlocks()->getEntries();
        		$message = implode(", ", array_map(function(BlockEntry $entry) {
        			return $entry->getName();
                }, $list));

        		$sender->sendMessage(Core::PREFIX . "Blocked Players (x" . count($list)  . "):");
        		$sender->sendMessage(TextFormat::GRAY . $message);
        	break;
        	case "ips":
        		$list = EssentialManager::getInstance()->getIPBlocks()->getEntries();
                $message = implode(", ", array_map(function(BlockEntry $entry) {
                	return $entry->getName();
                }, $list));

                $sender->sendMessage(Core::PREFIX . "Blocked Ips (x" . count($list)  . "):");
                $sender->sendMessage(TextFormat::GRAY . $message);
            break;
        }
	}
}
