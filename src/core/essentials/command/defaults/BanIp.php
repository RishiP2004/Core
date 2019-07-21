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

class BanIp extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("ban-ip", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.ban-ip.command");
        $this->setUsage("<player : ip> [reason] [timeFormat]");
        $this->setDescription("Ban an Ip or Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /ban-ip " . $this->getUsage());
            return false;
        }
		$this->core->getStats()->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user) or !preg_match("/^([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])$/", $args[0])) {
				$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player or Ip");
				return false;
			}
			$ip = $args[0];
			$player = null;

			if($user) {
				$ip = $user->getIp();
				$player = $this->core->getServer()->getPlayer($user->getName());
			}
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
			$banList = $this->core->getEssentials()->getIpBans();

			if($banList->isBanned($ip)) {
				$sender->sendMessage($this->core->getErrorPrefix() . $ip . " is already Ip-Banned");
				return false;
			} else {
				$banList->addBan($ip, $reason, $expires, $sender->getName());

				if($player instanceof CorePlayer) {
					$player->sendMessage($this->core->getPrefix() . "You have been Ip-Banned By: " . $sender->getName() . " for the Reason: " . $reason . ". Expires: " . $expire);
				}
				$sender->sendMessage($this->core->getPrefix() . "You have Ip-Banned " . $user->getName() . " for the Reason: " . $reason . ". Expires: " . $expire);
				$this->core->getServer()->broadcastMessage($this->core->getPrefix() . $user->getName() . " has been Ip-Banned by " . $sender->getName() . " for the Reason: "  . $reason . ". Expires: " . $expire);
				return true;
			}
        });
		return false;
    }
}