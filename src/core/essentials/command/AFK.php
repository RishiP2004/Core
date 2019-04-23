<?php

declare(strict_types = 1);

namespace core\essentials\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class AFK extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("afk", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.afk.command");
        $this->setUsage("[value] [player]");
        $this->setDescription("Set yours or a Player's AFK mode");
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
            if(!$sender->hasPermission($this->getPermission() . ".Other")) {
                $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
                return false;
            }
            $player = $this->core->getServer()->getPlayer($args[1]);

            if(!$player instanceof CorePlayer) {
                $sender->sendMessage($this->core->getErrorPrefix() . $args[1] . " is not Online");
                return false;
            }
            if(!$this->core->getStats()->getCoreUser($args[1])) {
                $sender->sendMessage($this->core->getErrorPrefix() . $args[1] . " is not a valid Player");
                return false;
            } else {
				if(isset($args[0])) {
					$AFK = $value;
				} else {
					$AFK = $player->isAFK() === false ? true : false;
				}
				$player->setAFK($AFK);
				$player->sendMessage($this->core->getPrefix() . $sender->getName() . " set your AFK to " . strtoupper($AFK));
				$sender->sendMessage($this->core->getPrefix() . "Set " . $player->getName() . "'s AFK mode to " . strtoupper($AFK));
				return true;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
			if(isset($args[0])) {
				$AFK = $value;
			} else {
				$AFK = $sender->isAFK() === false ? true : false;
			}
			$sender->setAFK($AFK);
			$sender->sendMessage($this->core->getPrefix() . "Set your AFK mode to " . strtoupper($AFK));
			return true;
        }
    }
}