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

class World extends PluginCommand {
    private $manager;

    public function __construct(Essentials $manager) {
        parent::__construct("world", Core::getInstance());

        $this->manager = $manager;

        $this->setPermission("core.essentials.command.world");
        $this->setUsage("<world> [player]");
        $this->setDescription("Teleport yourself or a Player to a World");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /world " . $this->getUsage());
            return false;
        }
		if(!$sender->getServer()->isLevelGenerated($args[0])){
            $sender->sendMessage(Core::ERROR_PREFIX . "World doesn't exist");
            return false;
		}
		if(!$sender->getServer()->isLevelLoaded($args[0])) {
			$sender->sendMessage(Core::ERROR_PREFIX . "Level is not loaded yet. Loading...");
			
			if(!$sender->getServer()->loadLevel($args[0])) {
                $sender->sendMessage(Core::ERROR_PREFIX . "The level couldn't be loaded");
                return false;
            }
		}
        if(isset($args[1])) {
            if(!$sender->hasPermission($this->getPermission() . ".other")) {
                $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
                return false;
            }
            $player = Server::getInstance()->getPlayer($args[0]);

            if(!$player instanceof CorePlayer) {
                $sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not Online");
                return false;
            } else {
                $player->teleport(Server::getInstance()->getLevelByName($args[0])->getSpawnLocation(), 0, 0);
                $sender->sendMessage(Core::PREFIX . "Teleported " . $player->getName() . " to the World: " . $args[0]);
                $player->sendMessage(Core::PREFIX . $sender->getName() . " Teleported you to the World: " . $args[0]);
                return true;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
            return false;
        } else {
			$sender->teleport(Server::getInstance()->getLevelByName($args[0])->getSpawnLocation(), 0, 0);
            $sender->sendMessage(Core::PREFIX . "Teleported to the World: " . $args[0]);
            return true;
        }
    }
}