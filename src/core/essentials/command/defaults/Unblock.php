<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Unblock extends PluginCommand {
	private $core;

    public function __construct(Core $core) {
        parent::__construct("unblock", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.command.unblock");
        $this->setUsage("<player>");
        $this->setDescription("Unblock a Player");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /unblock " . $this->getUsage());
            return false;
        }
		$this->core->getStats()->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
				return false;
			}
			$blockList = $this->core->getEssentials()->getNameBlocks();

			if(!$blockList->isBanned($user->getName())) {
				$sender->sendMessage($this->core->getErrorPrefix() . $user->getName() . " is not Blocked");
				return false;
			} else {
				$blockList->remove($user->getName());

				$player = $this->core->getServer()->getPlayer($user->getName());

				if($player instanceof CorePlayer) {
					$player->sendMessage($this->core->getPrefix() . "You have been Unblocked By: " . $sender->getName());
				}
				$sender->sendMessage($this->core->getPrefix() . "You have Unblocked " . $user->getName());
				$this->core->getServer()->broadcastMessage($this->core->getPrefix() . $user->getName() . " has been Unblocked by " . $sender->getName());
				return true;
			}
        });
		return false;
    }
}
