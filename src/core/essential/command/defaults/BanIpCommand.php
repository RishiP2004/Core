<?php

declare(strict_types = 1);

namespace core\essential\command\defaults;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\RawStringArgument;

use core\Core;

use core\player\CorePlayer;
use core\player\traits\PlayerCallTrait;

use core\essential\EssentialManager;

use core\utils\MathUtils;

use pocketmine\Server;

use pocketmine\command\CommandSender;

class BanIpCommand extends BaseCommand {
	use PlayerCallTrait;

	public function prepare() : void {
		$this->setPermission("ban-ip.command");
		$this->registerArgument(0, new RawStringArgument("type"));
		$this->registerArgument(1, new RawStringArgument("time", true));
		$this->registerArgument(2, new RawStringArgument("reason", true));
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

			if($banList->isBanned($ip)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $ip . " is already Ip-Banned");
				return false;
			} else {
				$expires = null;

				if(isset($args["time"]) && $args["time"] !== "i") {
					$expires = MathUtils::expirationStringToTimer($args["time"]);
				}
				$expire = $expires === null ? "Not provided" : $expires->format("m-d-Y H:i");

				if(isset($args["reason"])) {
					$reason = implode(" ", array_slice($args, 2));
				} else {
					$reason = "Not provided";
				}
				$banList->addBan($ip, $reason, $expires, $sender->getName());

				if($player instanceof CorePlayer) {
					$player->sendMessage(Core::PREFIX . "You have been Ip-Banned By: " . $sender->getName() . " for the Reason: " . $reason . ". Expires: " . $expire);
				}
				$sender->sendMessage(Core::PREFIX . "You have Ip-Banned " . $user->getName() . " for the Reason: " . $reason . ". Expires: " . $expire);
				Server::getInstance()->broadcastMessage(Core::PREFIX . $user->getName() . " has been Ip-Banned by " . $sender->getName() . " for the Reason: "  . $reason . ". Expires: " . $expire);
				return true;
			}
        });
	}
}