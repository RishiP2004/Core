<?php

declare(strict_types = 1);

namespace core\essential\command\defaults;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\RawStringArgument;

use core\Core;

use core\player\CorePlayer;
use core\player\command\args\OfflinePlayerArgument;
use core\player\traits\PlayerCallTrait;

use core\essential\EssentialManager;

use core\utils\MathUtils;

use pocketmine\Server;

use pocketmine\command\CommandSender;

class BlockCommand extends BaseCommand {
	use PlayerCallTrait;

	public function prepare() : void {
		$this->setPermission("block.command");
		$this->registerArgument(0, new OfflinePlayerArgument("player"));
		$this->registerArgument(1, new RawStringArgument("time", true));
		$this->registerArgument(2, new RawStringArgument("reason", true));
	}

	public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
		$this->getCoreUser($args["player"]->getName(), function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args["player"] . " is not a valid Player");
				return false;
			}
			$blockList = EssentialManager::getInstance()->getNameBlocks();

			if($blockList->isBanned($user->getName())) {
				$sender->sendMessage(Core::ERROR_PREFIX . $user->getName() . " is already Blocked");
				return false;
			} else {
				$expires = null;

				if(isset($args["time"]) && $args["time"] !== "i") {
					$expires = MathUtils::expirationStringToTimer($args["time"]);
				}
				$expire = $expires ?? "Not provided";

				if(isset($args["reason"])) {
					$reason = implode(" ", $args["reason"]);
				} else {
					$reason = "Not provided";
				}
				$blockList->addBan($user->getName(), $reason, $expires, $sender->getName());

				$player = Server::getInstance()->getPlayerByPrefix($user->getName());

				if($player instanceof CorePlayer) {
					$player->sendMessage(Core::PREFIX . "You have been Blocked By: " . $sender->getName() . " for the Reason: " . $reason . ". Expires: " . $expire);
				}
				$sender->sendMessage(Core::PREFIX . "You have Blocked " . $user->getName() . " for the Reason: " . $reason . ". Expires: " . $expire);
				Server::getInstance()->broadcastMessage(Core::PREFIX . $user->getName() . " has been Blocked by " . $sender->getName() . " for the Reason: "  . $reason . ". Expires: " . $expire);
				return true;
			}
        });
	}
}
