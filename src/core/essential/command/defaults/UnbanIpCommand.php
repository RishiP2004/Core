<?php

declare(strict_types = 1);

namespace core\essential\command\defaults;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\RawStringArgument;

use core\Core;

use core\player\CorePlayer;
use core\player\traits\PlayerCallTrait;

use core\essential\EssentialManager;

use pocketmine\Server;

use pocketmine\command\CommandSender;

class UnbanIpCommand extends BaseCommand {
	use PlayerCallTrait;

	public function prepare() : void {
		$this->setPermission("unban-ip.command");
		$this->registerArgument(0, new RawStringArgument("type", false));
	}

	public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
		$this->getCoreUser($args["type"], function($user) use ($sender, $args) {
			if(is_null($user) or !preg_match("/^([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])$/", $args["type"])) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args["type"] . " is not a valid Player or Ip");
				return false;
			}
			$ip = $args["type"];
			$player = null;

			if($user) {
				$ip = $user->getIp();
				$player = Server::getInstance()->getPlayerByPrefix($user->getName());
			}
			$banList = EssentialManager::getInstance()->getIpBans();

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
		return;
    }
}