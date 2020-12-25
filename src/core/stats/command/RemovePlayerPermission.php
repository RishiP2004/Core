<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use core\stats\Stats;

use pocketmine\Server;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\permission\Permission;

class RemovePlayerPermission extends PluginCommand {
    private $manager;

    public function __construct(Stats $manager) {
        parent::__construct("removeplayerpermission", Core::getInstance());

        $this->manager = $manager;

        $this->setAliases(["removepperm"]);
        $this->setPermission("core.stats.command.removeplayerpermissions");
        $this->setUsage("<player> <permission : all>");
        $this->setDescription("Remove a Permission from a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /removeplayerpermission " .  $this->getUsage());
            return false;
        }
		$this->manager->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
				return false;
			}
			if(!$user->hasPermission($args[1]) && strtolower($args[1]) !== "all") {
				$sender->sendMessage(Core::ERROR_PREFIX . $user->getName() . " doesn't have the Permission " . $args[1]);
				return false;
			} else {
				if(strtolower($args[1]) === "all") {
					$user->setPermissions([]);
					$sender->sendMessage(Core::PREFIX . "Removed all Permissions from " . $user->getName());
					return true;
				}
				$perm = new Permission($args[1]);
				
				$user->removePermission($perm);

				$player = Server::getInstance()->getPlayer($user->getName());
		
				if($player instanceof CorePlayer) {
					$player->sendMessage(Core::PREFIX . $sender->getName() . " Removed the Permission " . $perm->getName() . " from you");
				}
				$sender->sendMessage(Core::PREFIX . "Removed the Permission " . $perm->getName() . " from " . $user->getName());
				return true;
			}
        });
		return false;
    }
}