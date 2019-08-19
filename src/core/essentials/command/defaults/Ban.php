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

use pocketmine\utils\TextFormat;

class Ban extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("ban", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.command.ban");
        $this->setUsage("<player> [reason] [time]");
        $this->setDescription("Ban a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /ban " . $this->getUsage());
            return false;
        }
		$this->core->getStats()->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
				return false;
			}
			$banList = $this->core->getEssentials()->getNameBans();
			
			if($banList->isBanned($user->getName())) {
				$sender->sendMessage($this->core->getErrorPrefix() . $user->getName() . " is already Banned");
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
				$banList->addBan($user->getName(), $reason, $expires, $sender->getName());

				$player = $this->core->getServer()->getPlayer($user->getName());

				if($player instanceof CorePlayer) {
					$player->kick($this->core->getPrefix() . "You have been Banned By: " . $sender->getName() . "\n" . TextFormat::GRAY . "Reason: " . $reason . "\n" . TextFormat::GRAY . "Expires: " . $expire);
				}
				$sender->sendMessage($this->core->getPrefix() . "You have Banned " . $user->getName() . " for the Reason: " . $reason . ". Expires: " . $expire);
				$this->core->getServer()->broadcastMessage($this->core->getPrefix() . $user->getName() . " has been Banned by " . $sender->getName() . " for the Reason: "  . $reason . ". Expires: " . $expire);
				return true;
			}
		});
		return false;
    }
}