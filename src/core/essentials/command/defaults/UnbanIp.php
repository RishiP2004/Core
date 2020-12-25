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

class UnbanIp extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
		parent::__construct("unban-ip", Core::getInstance());

		$this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.unban-ip");
        $this->setUsage("<player : ip>");
        $this->setDescription("Un-Ip Ban a Player");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /unban-ip " . $this->getUsage());
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
			$banList = $this->manager->getIpBans();

			if(!$banList->isBanned($ip)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $ip . " is not Banned");
				return false;
			} else {
				$banList->remove($ip);

				if($player instanceof CorePlayer) {
					$player->sendMessage(Core::PREFIX . "You have been Un-Ip Banned By: " . $sender->getName());
				}
				$sender->sendMessage(Core::PREFIX . "You have Un-Ip Banned " . $user->getName());
				Server::getInstance()->broadcastMessage(Core::PREFIX . $user->getName() . " has been Un-Ip Banned by " . $sender->getName());
				return true;
			}
        });
		return false;
    }
}