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

class Mute extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("mute", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.ban.command");
        $this->setUsage("<player> [reason] [timeFormat]");
        $this->setDescription("Mute a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /mute" . " " . $this->getUsage());
            return false;
        }
		$this->core->getStats()->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
				return false;
			}
			$muteList = $this->core->getEssentials()->getNameMutes();

			if($muteList->isBanned($user->getName())) {
				$sender->sendMessage($this->core->getErrorPrefix() . $user->getName() . " is already Muted");
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
				$muteList->addBan($user->getName(), $reason, $expires, $sender->getName());

				$player = $this->core->getServer()->getPlayer($user->getName());

				if($player instanceof CorePlayer) {
					$player->sendMessage($this->core->getPrefix() . "You have been Muted By: " . $sender->getName() . " for the Reason: " . $reason . ". Expires: " . $expire);
				}
				$sender->sendMessage($this->core->getPrefix() . "You have Muted " . $user->getName() . " for the Reason: " . $reason . ". Expires: " . $expire);
				$this->core->getServer()->broadcastMessage($this->core->getPrefix() . $user->getName() . " has been Muted by " . $sender->getName() . " for the Reason: "  . $reason . ". Expires: " . $expire);
				return true;
			}
		});
		return false;
    }
}

