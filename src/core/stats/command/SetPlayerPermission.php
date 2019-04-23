<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class SetPlayerPermission extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("setplayerpermission", $core);

        $this->core = $core;

        $this->setAliases(["setpperm"]);
        $this->setPermission("core.stats.command.setplayerpermissions");
        $this->setUsage("<player> <permission(s)>");
        $this->setDescription("Set Permissions of a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /setplayerpermission" . " " . $this->getUsage());
            return false;
		}
        $user = $this->core->getStats()->getCoreUser($args[0]);
		
		if(!$user) {
			$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
			return false;
        } else {
            $user->setPermission(explode(", ", $args[1]));

            $player = $this->core->getServer()->getPlayer($user->getName());
		
			if($player instanceof CorePlayer) {
				$player->sendMessage($this->core->getPrefix() . $sender->getName() . " set your Permission(s) to " . implode(", ", $args[1]));
			}
            $sender->sendMessage($this->core->getPrefix() . "Set the Permission(s) of " . $user->getName() . " to " . implode(", ", $args[1]));
            return true;
        }
    }
}