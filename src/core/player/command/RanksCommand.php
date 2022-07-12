<?php

declare(strict_types = 1);

namespace core\player\command;

use core\Core;

use core\player\PlayerManager;
use core\player\rank\Rank;

use CortexPE\Commando\BaseCommand;

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class RanksCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("ranks.command");
	}
    
    public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
        $sender->sendMessage(Core::PREFIX . "Ranks:");
            
        foreach(PlayerManager::getInstance()->getRanksFlat() as $rank) {
        	if($rank instanceof Rank) {
        		$sender->sendMessage(TextFormat::GRAY . "- " . $rank->getName() . ": " . TextFormat::YELLOW . $rank->getValue());
        	}
        }
    }
}
