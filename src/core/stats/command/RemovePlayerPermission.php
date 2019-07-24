<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\permission\Permission;

class RemovePlayerPermission extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("removeplayerpermission", $core);

        $this->core = $core;

        $this->setAliases(["removepperm"]);
        $this->setPermission("core.stats.command.removeplayerpermissions");
        $this->setUsage("<player> <permission : all>");
        $this->setDescription("Remove a Permission from a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /removeplayerpermission " .  $this->getUsage());
            return false;
        }
		$this->core->getStats()->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
				return false;
			}
			if(!$user->hasPermission($args[1]) && strtolower($args[1]) !== "all") {
				$sender->sendMessage($this->core->getPrefix() . $user->getName() . " doesn't have the Permission " . $args[1]);
				return false;
			} else {
				if(strtolower($args[1]) === "all") {
					$user->setPermissions([]);
					$sender->sendMessage($this->core->getPrefix() . "Removed all Permissions from " . $user->getName());
					return true;
				}
				$perm = new Permission($args[1]);
				
				$user->removePermission($perm);

				$player = $this->core->getServer()->getPlayer($user->getName());
		
				if($player instanceof CorePlayer) {
					$player->sendMessage($this->core->getPrefix() . $sender->getName() . " Removed the Permission " . $perm->getName() . " from you");
				}
				$sender->sendMessage($this->core->getPrefix() . "Removed the Permission " . $perm->getName() . " from " . $user->getName());
				return true;
			}
        });
		return false;
    }
}