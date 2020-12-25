<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use core\essentials\Essentials;

use core\stats\Stats;

use pocketmine\Server;

use pocketmine\command\{
	PluginCommand,
	CommandSender
};

class UnmuteIp extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
		parent::__construct("unmute-ip", Core::getInstance());

		$this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.unmute-ip");
        $this->setUsage("<player : ip>");
        $this->setDescription("Un-Ip Block a Player");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /unmute-ip " . $this->getUsage());
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
			$muteList = $this->manager->getIpMutes();

			if(!$muteList->isBanned($ip)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $ip . " is not Muted");
				return false;
			} else {
				$muteList->remove($ip);

				if($player instanceof CorePlayer) {
					$player->sendMessage(Core::PREFIX . "You have been Un-Ip Muted By: " . $sender->getName());
				}
				$sender->sendMessage(Core::PREFIX . "You have Un-Ip Muted " . $user->getName());
				Server::getInstance()->broadcastMessage(Core::PREFIX . $user->getName() . " has been Un-Ip Muted by " . $sender->getName());
				return true;
			}
        });
		return false;
    }
}