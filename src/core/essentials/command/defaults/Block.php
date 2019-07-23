<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use core\utils\Math;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Block extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("block", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.command.block");
        $this->setUsage("<player> [reason] [timeFormat]");
        $this->setDescription("Block a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /block " . $this->getUsage());
            return false;
        }
		$this->core->getStats()->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
				return false;
			}
			$blockList = $this->core->getEssentials()->getNameBlocks();

			if($blockList->isBanned($user->getName())) {
				$sender->sendMessage($this->core->getErrorPrefix() . $user->getName() . " is already Blocked");
				return false;
			} else {
				$expires = null;

				if(isset($args[2])) {
					$expires = Math::expirationStringToTimer($args[2]);
				}
				$expire = $expires ?? "Not provided";
				
				if(isset($args[1])) {
					$reason = implode(" ", $args[1]);
				} else {
					$reason = "Not provided";
				}
				$blockList->addBan($user->getName(), $reason, $expires, $sender->getName());

				$player = $this->core->getServer()->getPlayer($user->getName());

				if($player instanceof CorePlayer) {
					$player->sendMessage($this->core->getPrefix() . "You have been Blocked By: " . $sender->getName() . " for the Reason: " . $reason . ". Expires: " . $expire);
				}
				$sender->sendMessage($this->core->getPrefix() . "You have Blocked " . $user->getName() . " for the Reason: " . $reason . ". Expires: " . $expire);
				$this->core->getServer()->broadcastMessage($this->core->getPrefix() . $user->getName() . " has been Blocked by " . $sender->getName() . " for the Reason: "  . $reason . ". Expires: " . $expire);
				return true;
			}
        });
		return false;
    }
}
