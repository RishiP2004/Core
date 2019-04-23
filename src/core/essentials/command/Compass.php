<?php

declare(strict_types = 1);

namespace core\essentials\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Compass extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("compass", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.compass.command");
        $this->setUsage("[player]");
        $this->setDescription("Check what Direction you or a Player is Facing");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(isset($args[0])) {
			if(!$sender->hasPermission($this->getPermission() . ".Other")) {
				$sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
				return false;
			}
			$player = $this->core->getServer()->getPlayer($args[0]);

            if(!$player instanceof CorePlayer) {
                $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not Online");
                return false;
            }
            if(!$this->core->getStats()->getCoreUser($args[0])) {
                $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
                return false;
            } else {
				switch($player->getDirection()) {
					case 0:
						$direction = "South";
					break;
					case 1:
						$direction = "West";
					break;
					case 2:
						$direction = "North";
					break;
					case 3:
						$direction = "East";
					break;
					default:
						$sender->sendMessage($this->core->getErrorPrefix() . "There was an error while getting " . $player->getName() . "'s Direction");
						return false;
					break;
				}
				$sender->sendMessage($this->core->getPrefix() . $player->getName() . " is Facing " . $direction);
                return true;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
			switch($sender->getDirection()) {
				case 0:
					$direction = "South";
				break;
				case 1:
					$direction = "West";
				break;
				case 2:
					$direction = "North";
				break;
				case 3:
					$direction = "East";
				break;
				default:
					$sender->sendMessage($this->core->getErrorPrefix() . "There was an error while getting your Direction");
					return false;
				break;
			}
			$sender->sendMessage($this->core->getPrefix() . "You are Facing " . $direction);
            return true;
        }
    }
}