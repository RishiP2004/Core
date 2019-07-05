<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class DeleteAccount extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("deleteaccount", $core);

        $this->core = $core;

        $this->setAliases(["delaccount", "deleteacc"]);
        $this->setPermission("core.stats.command.deleteaccount");
        $this->setUsage("<player>");
        $this->setDescription("Delete a Player's Account");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /deleteaccount" . " " . $this->getUsage());
            return false;
        }
		$this->core->getStats()->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
				return false;
			} else {
				$this->core->getStats()->unregisterCoreUser($user);

				$player = $this->core->getServer()->getPlayer($user->getName());
		
				if($player instanceof CorePlayer) {
					$player->kick($this->core->getPrefix() . $sender->getName() . "Deleted your Account");
				}
				$sender->sendMessage($this->core->getPrefix() . "Deleted " . $user->getName() . "'s Account");
				return true;
			}
        });
		return false;
    }
}