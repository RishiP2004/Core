<?php

declare(strict_types = 1);

namespace core\essentials\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Location extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("location", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.command.location");
        $this->setUsage("[player]");
		$this->setAliases(["loc", "compass", "xyz"]);
        $this->setDescription("Check your Location and Direction or a Players'");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(isset($args[0])) {
			if(!$sender->hasPermission($this->getPermission() . ".other")) {
				$sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
				return false;
			}
			$player = $this->core->getServer()->getPlayer($args[0]);

            if(!$player instanceof CorePlayer) {
                $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not Online");
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
				$sender->sendMessage($this->core->getPrefix() . $player->getName() . " is at Coordinates: X: " . (int) $player->getX() . ", Y: " . (int) $player->getY() . ", Z: " . (int) $player->getZ() . " at the World: " . $player->getLevel()->getName() . " and is Facing " . $direction);
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
			$sender->sendMessage($this->core->getPrefix() . "You are at Coordinates: X: " . (int) $sender->getX() . ", Y: " . (int) $sender->getY() . ", Z: " . (int) $sender->getZ() . " at the World: " . $sender->getLevel()->getName() . " while Facing " . $direction);
            return true;
        }
    }
}