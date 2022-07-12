<?php

declare(strict_types = 1);

namespace core\essential\command\defaults;

use CortexPE\Commando\BaseCommand;

use core\Core;

use core\player\CorePlayer;
use core\player\command\args\PlayerArgument;

use core\network\NetworkManager;

use pocketmine\Server;

use pocketmine\command\CommandSender;

use pocketmine\entity\Location;

class SpawnCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("spawn.command");
		$this->registerArgument(0, new PlayerArgument("player", true));
	}

	public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
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
            	if(($this->getName() === "lobby" or $this->getName() === "hub") && !$player->getCoreUser()->getServer() instanceof Lobby) {
            		$player->transfer(NetworkManager::getInstance()->getServer("Lobby")->getIp(), NetworkManager::getInstance()->getServer("Lobby")->getPort());
					$sender->sendMessage(Core::PREFIX . "Sent " . $player->getName() . " to the Hub");
					$player->sendMessage(Core::PREFIX . $sender->getName() . " Sent you to the Hub");
					return ;
				}
            	$player->teleport(Location::fromObject(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn()->asPosition(), Server::getInstance()->getWorldManager()->getDefaultWorld()));
				$sender->sendMessage(Core::PREFIX . "Teleported " . $player->getName() . " to SpawnCommand");
				$player->sendMessage(Core::PREFIX . $sender->getName() . " Teleported you to SpawnCommand");
                return;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
		} else {
			if(($this->getName() === "lobby" or $this->getName() === "hub") && !$sender->getCoreUser()->getServer()->getName() == "Lobby") {
				$sender->transfer(NetworkManager::getInstance()->getServer("Lobby")->getIp(), NetworkManager::getInstance()->getServer("Lobby")->getPort());
				$sender->sendMessage(Core::PREFIX . "Transferred to the Hub");
				return;
			}
			$sender->teleport(Location::fromObject(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSafeSpawn()->asPosition(), Server::getInstance()->getWorldManager()->getDefaultWorld()));
			$sender->sendMessage(Core::PREFIX . "Teleported to Spawn");
		}
    }
}