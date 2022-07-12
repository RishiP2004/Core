<?php

declare(strict_types = 1);

namespace core\essential\command;

use core\Core;

use core\player\CorePlayer;
use core\player\command\args\PlayerArgument;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\RawStringArgument;

use pocketmine\Server;

use pocketmine\command\CommandSender;

class WorldCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("world.command");
		$this->registerArgument(0, new RawStringArgument("world"));
		$this->registerArgument(1, new PlayerArgument("player", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		if(!$sender->getServer()->getWorldManager()->isWorldGenerated($args["world"])) {
			$sender->sendMessage(Core::ERROR_PREFIX . "World doesn't exist");
			return;
		}
		if(!$sender->getServer()->getWorldManager()->isWorldLoaded($args["world"])) {
			$sender->sendMessage(Core::ERROR_PREFIX . "World is not loaded yet. Loading...");

			if(!$sender->getServer()->getWorldManager()->loadWorld($args["world"])) {
				$sender->sendMessage(Core::ERROR_PREFIX . "The world couldn't be loaded");
				return;
			}
		}
		if(isset($args["player"])) {
			if(!$sender->hasPermission($this->getPermission() . ".other")) {
				$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
				return;
			}
			$args["player"]->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args["world"])->getSpawnLocation());
			$sender->sendMessage(Core::PREFIX . "Teleported " . $args["player"]->getName() . " to the World: " . $args["world"]);
			$args["player"]->sendMessage(Core::PREFIX . $sender->getName() . " Teleported you to the World: " . $args["world"]);
			return;
		}
		if(!$sender instanceof CorePlayer) {
			$sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
			return;
		}
		$sender->teleport(Server::getInstance()->getWorldManager()->getWorldByName($args["world"])->getSpawnLocation());
		$sender->sendMessage(Core::PREFIX . "Teleported to the World: " . $args["world"]);
	}
}