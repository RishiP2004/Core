<?php

declare(strict_types = 1);

namespace core\essential\command;

use core\Core;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\constraint\InGameRequiredConstraint;

use pocketmine\command\CommandSender;

//use hcf\discord\Logger;
use pocketmine\Server;

use pocketmine\utils\TextFormat;

class ReplyCommand extends BaseCommand {
  	public function prepare() : void {
  		$this->setPermission("reply.command");
  		$this->addConstraint(new InGameRequiredConstraint($this));
		$this->registerArgument(0, new RawStringArgument("message"));
	}
    
    public function onRun(CommandSender $sender, string $commandLabel, array $args): void {
		if(is_null($sender->getRecentMessager())) {
			$sender->sendMessage(Core::ERROR_PREFIX . "You have not received a message recently");
			return;
		}
        $player = Server::getInstance()->getPlayerByPrefix($sender->getRecentMessager());

        if($player === null) {
        	$sender->sendMessage(Core::ERROR_PREFIX . "Player is offline");
        	return;
        }
        $message = implode(" ", $args["message"]);
        $sender->sendMessage(TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "§r§7(To " . "{$player->getCoreUser()->getRank()->getColoredName()}§r {$player->getName()}§r§7) " . TextFormat::RESET . TextFormat::GRAY . $message);
        $player->sendMessage(TextFormat::DARK_PURPLE . TextFormat::BOLD . "§r§7(From " . "{$sender->getCoreUser()->getRank()->getColoredName()}§r {$sender->getName()}§r§7) " . TextFormat::RESET . TextFormat::GRAY . $message);
        //Logger::sendTellLog($sender->getName(), $player->getName(), $message);
    }
}
