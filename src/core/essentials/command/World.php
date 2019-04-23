<?php

declare(strict_types = 1);

namespace core\essentials\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class World extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("world", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.world.command");
        $this->setUsage("<world> [player]");
        $this->setDescription("Teleport yourself or a Player to a World");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /world" . " " . $this->getUsage());
            return false;
        }
		if(!$sender->getServer()->isLevelGenerated($args[0])){
            $sender->sendMessage($this->core->getErrorPrefix() . "World doesn't exist");
            return false;
		}
		if(!$sender->getServer()->isLevelLoaded($args[0])) {
			$sender->sendMessage($this->core->getErrorPrefix() . "Level is not loaded yet. Loading...");
			
			if(!$sender->getServer()->loadLevel($args[0])) {
                $sender->sendMessage($this->core->getErrorPrefix() . "The level couldn't be loaded");
                return false;
            }
		}
        if(isset($args[1])) {
            if(!$sender->hasPermission($this->getPermission() . ".other")) {
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
                $player->teleport($this->core->getServer()->getLevelByName($args[0])->getSpawnLocation(), 0, 0);
                $sender->sendMessage($this->core->getPrefix() . "Teleported " . $player->getName() . " to the World: " . $args[0]);
                $player->sendMessage($this->core->getPrefix() . $sender->getName() . " Teleported you to the World: " . $args[0]);
                return true;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
			$sender->teleport($this->core->getServer()->getLevelByName($args[0])->getSpawnLocation(), 0, 0);
            $sender->sendMessage($this->core->getPrefix() . "Teleported to the World: " . $args[0]);
            return true;
        }
    }
}