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

class AddPlayerPermission extends PluginCommand {
    private $manager;

    public function __construct(Stats $manager) {
        parent::__construct("addplayerpermission", Core::getInstance());

        $this->manager = $manager;

        $this->setAliases(["addpperm"]);
        $this->setPermission("core.stats.command.addplayerpermissions");
        $this->setUsage("<player> <permission>");
        $this->setDescription("Add a Permission to a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /addplayerpermission " . $this->getUsage());
            return false;
        }
		$this->manager->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
				return false;
			}
			if($user->hasPermission($args[1])) {
				$sender->sendMessage(Core::ERROR_PREFIX . $user->getName() . " already has the Permission " . $args[1]);
				return false;
			} else {
				$perm = new Permission($args[1]);
				$user->addPermission($perm);

				$player = Server::getInstance()->getPlayer($user->getName());
		
				if($player instanceof CorePlayer) {
					$player->sendMessage(Core::PREFIX . $sender->getName() . " gave you the Permission " . $perm->getName());
				}
				$sender->sendMessage(Core::PREFIX . "Added the Permission " . $perm->getName() . " to " . $user->getName());
				return true;
			}
        });
		return false;
    }
}