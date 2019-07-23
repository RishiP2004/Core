<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class ListPlayerPermissions extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("listplayerpermissions", $core);

        $this->core = $core;

        $this->setAliases(["listpperm"]);
        $this->setPermission("core.stats.command.listplayerpermissions");
        $this->setUsage("<player>");
        $this->setDescription("List all Permissions of a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /listplayerpermissions " .  $this->getUsage());
            return false;
        }
		$this->core->getStats()->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
				return false;
			} else {
				$sender->sendMessage($this->core->getPrefix() . $user->getName() . "'s Permissions:");
				
				if(empty($user->getPermissions()) or !is_array($user->getPermissions())) {
					$sender->sendMessage(TextFormat::GRAY . "None");
					return true;
				}
				$sender->sendMessage(TextFormat::GRAY . implode(", ", (array) $user->getPermissions()));
				return true;
			}	
        });
		return false;
    }
}