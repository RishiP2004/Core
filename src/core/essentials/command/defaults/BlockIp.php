<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use core\essentials\Essentials;

use core\stats\Stats;

use core\utils\Math;

use pocketmine\Server;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class BlockIp extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
		parent::__construct("block-ip", Core::getInstance());

        $this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.block-ip");
        $this->setUsage("<player : ip> [time] [reason]");
        $this->setDescription("Block an Ip or Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /block-ip " . $this->getUsage());
            return false;
        }
		Stats::getInstance()->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user) or !preg_match("/^([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])$/", $args[0])) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player or Ip");
				return false;
			}
			$ip = $args[0];
			$player = null;

			if($user) {
				$ip = $user->getIp();
				$player = Server::getInstance()->getPlayer($user->getName());
			}
			$blockList = $this->manager->getIpBlocks();

			if($blockList->isBanned($ip)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $ip . " is already Ip-Blocked");
				return false;
			} else {
				$expires = null;

				if(isset($args[1]) && $args[1] !== "i") {
					$expires = Math::expirationStringToTimer($args[1]);
				}
				$expire = $expires ?? "Not provided";

				if(isset($args[2])) {
					$reason = implode(" ", $args[2]);
				} else {
					$reason = "Not provided";
				}
				$blockList->addBan($ip, $reason, $expires, $sender->getName());

				if($player instanceof CorePlayer) {
					$player->sendMessage(Core::PREFIX . "You have been Ip-Blocked By: " . $sender->getName() . " for the Reason: " . $reason . ". Expires: " . $expire);
				}
				$sender->sendMessage(Core::PREFIX . "You have Ip-Blocked " . $user->getName() . " for the Reason: " . $reason . ". Expires: " . $expire);
				Server::getInstance()->broadcastMessage(Core::PREFIX . $user->getName() . " has been Ip-Blocked by " . $sender->getName() . " for the Reason: "  . $reason . ". Expires: " . $expire);
				return true;
			}
        });
		return false;
    }
}