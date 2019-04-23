<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\level\Location;

class Spawn extends PluginCommand {
    private $core;
    
    public function __construct(Core $core) {
        parent::__construct("spawn", $core);
       
        $this->core = $core;
       
        $this->setPermission("core.essentials.defaults.spawn.command");
        $this->setUsage("[player]");
        $this->setDescription("Go to Spawn or send a Player there");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        }
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
            if(!$user = $this->core->getStats()->getCoreUser($args[0])) {
                $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
                return false;
            } else {
                $player->teleport(Location::fromObject($this->core->getServer()->getDefaultLevel()->getSpawnLocation(), $this->core->getServer()->getDefaultLevel()));
				$sender->sendMessage($this->core->getPrefix() . "Teleported " . $user->getName() . " to Spawn");
				$player->sendMessage($this->core->getPrefix() . $sender->getName() . " Teleported you to Spawn");
                return true;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
			$sender->teleport(Location::fromObject($this->core->getServer()->getDefaultLevel()->getSpawnLocation(), $this->core->getServer()->getDefaultLevel()));
            $sender->sendMessage($this->core->getPrefix() . "Teleported to Spawn");
            return true;
        }
    }
}