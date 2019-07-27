<?php

declare(strict_types = 1);

namespace core\essentials\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Fly extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("fly", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.command.fly");
        $this->setUsage("[value] [player]");
        $this->setDescription("Set yours or a Player's Fly mode");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
		$value = false;
		
		if(isset($args[0])) {
			switch(strtolower($args[0])) {
				case "true":
				case "on":
					$value = true;
				break;
				case "false":
				case "off":
					$value = false;
				break;	
				default:
					$sender->sendMessage($this->core->getErrorPrefix() . $value . " is not a valid Boolean");
				break;
			}
		}
        if(isset($args[1])) {
            if(!$sender->hasPermission($this->getPermission() . ".other")) {
                $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
                return false;
            }
            $player = $this->core->getServer()->getPlayer($args[1]);

            if(!$player instanceof CorePlayer) {
                $sender->sendMessage($this->core->getErrorPrefix() . $args[1] . " is not Online");
                return false;
            } else {
				if(isset($args[0])) {
					$flying = $value;
				} else {
					$flying = $player->flying() === false ? true : false;
				}
				$player->setFly($flying);
				
				$str = $player->flying() === false ? "False" : "True";
				
				$player->sendMessage($this->core->getPrefix() . $sender->getName() . " set your Fly mode to " . $str);
				$sender->sendMessage($this->core->getPrefix() . "Set " . $player->getName() . "'s Fly mode to " . $str);
				return true;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
			if(isset($args[0])) {
				$flying = $value;
			} else {
				$flying = $sender->flying() === false ? true : false;
			}
			$sender->setFly($flying);
			
			$str = $sender->flying() === false ? "False" : "True";
			
			$sender->sendMessage($this->core->getPrefix() . "Set your Fly mode to " . $str);
			return true;
        }
    }
}