<?php

declare(strict_types = 1);

namespace core\essentials\command;

use core\Core;
use core\CorePlayer;

use core\essentials\Essentials;

use pocketmine\Server;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Fly extends PluginCommand {
    private $manager;

    public function __construct(Essentials $manager) {
        parent::__construct("fly", Core::getInstance());

        $this->manager = $manager;

        $this->setPermission("core.essentials.command.fly");
        $this->setUsage("[value] [player]");
        $this->setDescription("Set yours or a Player's Fly mode");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
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
					$sender->sendMessage(Core::ERROR_PREFIX . $value . " is not a valid Boolean");
					return false;
				break;
			}
		}
        if(isset($args[1])) {
            if(!$sender->hasPermission($this->getPermission() . ".other")) {
                $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
                return false;
            }
            $player = Server::getInstance()->getPlayer($args[1]);

            if(!$player instanceof CorePlayer) {
                $sender->sendMessage(Core::ERROR_PREFIX . $args[1] . " is not Online");
                return false;
            } else {
				if(isset($args[0])) {
					$flying = $value;
				} else {
					$flying = $player->flying() === false ? true : false;
				}
				$player->setFly($flying);
				
				$str = $player->flying() === true ? "True" : "False";
				
				$player->sendMessage(Core::PREFIX . $sender->getName() . " set your Fly mode to " . $str);
				$sender->sendMessage(Core::PREFIX . "Set " . $player->getName() . "'s Fly mode to " . $str);
				return true;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
            return false;
        } else {
			if(isset($args[0])) {
				$flying = $value;
			} else {
				$flying = $sender->flying() === false ? true : false;
			}
			$sender->setFly($flying);
			
			$str = $sender->flying() === true ? "True" : "False";
			
			$sender->sendMessage(Core::PREFIX . "Set your Fly mode to " . $str);
			return true;
        }
    }
}