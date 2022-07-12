<?php

declare(strict_types = 1);

namespace core\essential\command;

use core\Core;

use core\player\CorePlayer;
use core\player\command\args\PlayerArgument;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\BooleanArgument;

use pocketmine\command\CommandSender;

class FlyCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("fly.command");
		$this->registerArgument(0, new BooleanArgument("value", false));
		$this->registerArgument(1, new PlayerArgument("player", true));
	}

    public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
        if(isset($args["value"])) {
            if(!$sender->hasPermission($this->getPermission() . ".other")) {
                $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
                return;
            }
			if(isset($args["value"])) {
				$flying = $args["value"];
			} else {
				$flying = $args["player"]->flying() === false;
			}
			$args["player"]->setFly($flying);
				
			$str = $args["player"]->flying() === true ? "True" : "False";

			$args["player"]->sendMessage(Core::PREFIX . $sender->getName() . " set your Fly mode to " . $str);
			$sender->sendMessage(Core::PREFIX . "Set " . $args["player"]->getName() . "'s Fly mode to " . $str);
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
        } else {
        	if(!$sender->hasPermission($this->getPermission())) {
				$sender->sendMessage(Core::ERROR_PREFIX . "Cannot Fly in this Server");
        		return;
			}
			if(isset($args["value"])) {
				$flying = $args["value"];
			} else {
				$flying = $sender->flying() === false;
			}
			$sender->setFly($flying);
			
			$str = $sender->flying() === true ? "True" : "False";
			
			$sender->sendMessage(Core::PREFIX . "Set your Fly mode to " . $str);
        }
    }
}