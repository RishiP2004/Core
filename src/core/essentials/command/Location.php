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

class Location extends PluginCommand {
    private $manager;

    public function __construct(Essentials $manager) {
        parent::__construct("location", Core::getInstance());

        $this->manager = $manager;

        $this->setPermission("core.essentials.command.location");
        $this->setUsage("[player]");
		$this->setAliases(["loc", "compass", "xyz"]);
        $this->setDescription("Check your Location and Direction or a Players'");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(isset($args[0])) {
			if(!$sender->hasPermission($this->getPermission() . ".other")) {
				$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
				return false;
			}
			$player = Server::getInstance()->getPlayer($args[0]);

            if(!$player instanceof CorePlayer) {
                $sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not Online");
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
						$sender->sendMessage(Core::ERROR_PREFIX . "There was an error while getting " . $player->getName() . "'s Direction");
						return false;
					break;
				}
				$sender->sendMessage(Core::PREFIX . $player->getName() . " is at Coordinates: X: " . (int) $player->getX() . ", Y: " . (int) $player->getY() . ", Z: " . (int) $player->getZ() . " at the World: " . $player->getLevel()->getName() . " and is Facing " . $direction);
                return true;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
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
					$sender->sendMessage(Core::ERROR_PREFIX . "There was an error while getting your Direction");
					return false;
				break;
			}
			$sender->sendMessage(Core::PREFIX . "You are at Coordinates: X: " . (int) $sender->getX() . ", Y: " . (int) $sender->getY() . ", Z: " . (int) $sender->getZ() . " at the World: " . $sender->getLevel()->getName() . " while Facing " . $direction);
            return true;
        }
    }
}