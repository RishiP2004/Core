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

class BlockIpCommand extends BaseCommand {
	use PlayerCallTrait;

	public function prepare() : void {
		$this->setPermission("block-ip.command");
		$this->registerArgument(0, new RawStringArgument("type", false));
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
			$blockList = EssentialManager::getInstance()->getIpBlocks();

			if($blockList->isBanned($ip)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $ip . " is already Ip-Blocked");
				return false;
			} else {
				$expires = null;

				if(isset($args["time"]) && $args["time"] !== "i") {
					$expires = MathUtils::expirationStringToTimer($args[1]);
				}
				$expire = $expires ?? "Not provided";

				if(isset($args["reason"])) {
					$reason = implode(" ", $args["reason"]);
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
	}
}