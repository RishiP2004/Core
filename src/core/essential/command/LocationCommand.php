<?php

declare(strict_types = 1);

namespace core\essential\command;

use core\Core;

use core\player\CorePlayer;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\RawStringArgument;

use pocketmine\Server;

use pocketmine\command\CommandSender;

class LocationCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("location.command");
		$this->registerArgument(0, new RawStringArgument("player", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		if(isset($args["player"])) {
			if(!$sender->hasPermission($this->getPermission() . ".other")) {
				$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
				return;
			}
			$player = Server::getInstance()->getPlayerByPrefix($args["player"]);

			if(!$player instanceof CorePlayer) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args["player"] . " is not Online");
				return;
			} else {
				switch($player->getDirectionPlane()) {
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
						return;
				}
				$sender->sendMessage(Core::PREFIX . $player->getName() . " is at Coordinates: X: " . (int) $player->getPosition()->getX() . ", Y: " . (int) $player->getPosition()->getY() . ", Z: " . (int) $player->getPosition()->getZ() . " at the World: " . $player->getWorld()->getFolderName() . " and is Facing " . $direction);
			}
		}
		if(!$sender instanceof CorePlayer) {
			$sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
			return;
		}
		switch($sender->getDirectionPlane()) {
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
				return;
		}
		$sender->sendMessage(Core::PREFIX . "You are at Coordinates: X: " . (int) $sender->getPosition()->getX() . ", Y: " . (int) $sender->getPosition()->getY() . ", Z: " . (int) $sender->getPosition()->getZ() . " at the WorldManager: " . $sender->getWorld()->getFolderName() . " while Facing " . $direction);
	}
}