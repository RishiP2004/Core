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

class AddPlayerPermission extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("addplayerpermission", $core);

        $this->core = $core;

        $this->setAliases(["addpperm"]);
        $this->setPermission("core.stats.command.addplayerpermissions");
        $this->setUsage("<player> <permission>");
        $this->setDescription("Add a Permission to a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /addplayerpermission " . $this->getUsage());
            return false;
        }
		$this->core->getStats()->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
				return false;
			}
			if($user->hasPermission($args[1])) {
				$sender->sendMessage($this->core->getPrefix() . $user->getName() . " already has the Permission " . $args[1]);
				return false;
			} else {
				$perm = new Permission($args[1]);
				$user->addPermission($perm);

				$player = $this->core->getServer()->getPlayer($user->getName());
		
				if($player instanceof CorePlayer) {
					$player->sendMessage($this->core->getPrefix() . $sender->getName() . " gave you the Permission " . $perm->getName());
				}
				$sender->sendMessage($this->core->getPrefix() . "Added the Permission " . $perm->getName() . " to " . $user->getName());
				return true;
			}
        });
		return false;
    }
}