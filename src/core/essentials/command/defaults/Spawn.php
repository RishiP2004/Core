<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use core\essentials\Essentials;
use core\network\Network;
use core\network\server\Lobby;

use pocketmine\Server;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\level\Location;

class Spawn extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
		parent::__construct("spawn", Core::getInstance());

		$this->manager = $manager;
       
        $this->setPermission("core.essentials.defaults.command.spawn");
        $this->setUsage("[player]");
        $this->setAliases(["lobby", "hub"]);
        $this->setDescription("Go to Spawn or send a Player there");
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
            	if(($this->getName() === "lobby" or $this->getName() === "hub") && !$player->getCoreUser()->getServer() instanceof Lobby) {
            		$player->transfer(Network::getInstance()->getServer("Lobby")->getIp(), Network::getInstance()->getServer("Lobby")->getPort());
					$sender->sendMessage(Core::PREFIX . "Sent " . $player->getName() . " to the Hub");
					$player->sendMessage(Core::PREFIX . $sender->getName() . " Sent you to the Hub");
					return true;
				}
            	$player->teleport(Location::fromObject(Server::getInstance()->getDefaultLevel()->getSpawnLocation(), Server::getInstance()->getDefaultLevel()));
				$sender->sendMessage(Core::PREFIX . "Teleported " . $player->getName() . " to Spawn");
				$player->sendMessage(Core::PREFIX . $sender->getName() . " Teleported you to Spawn");
                return true;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
            return false;
        } else {
			if(($this->getName() === "lobby" or $this->getName() === "hub") && !$sender->getCoreUser()->getServer() instanceof Lobby) {
				$sender->transfer(Network::getInstance()->getServer("Lobby")->getIp(), Network::getInstance()->getServer("Lobby")->getPort());
				$sender->sendMessage(Core::PREFIX . "Transferred to the Hub");
				return true;
			}
			$sender->teleport(Location::fromObject(Server::getInstance()->getDefaultLevel()->getSpawnLocation(), Server::getInstance()->getDefaultLevel()));
			$sender->sendMessage(Core::PREFIX . "Teleported to Spawn");
			return true;
        }
    }
}