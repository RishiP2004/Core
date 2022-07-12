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

use pocketmine\utils\TextFormat;

class BanCommand extends BaseCommand {
	use PlayerCallTrait;

    public function prepare() : void {
    	$this->setPermission("ban.command");
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
			$banList = EssentialManager::getInstance()->getNameBans();
			
			if($banList->isBanned($user->getName())) {
				$sender->sendMessage(Core::ERROR_PREFIX . $user->getName() . " is already Banned");
				return false;
			} else {
				$expires = null;

				if(isset($args["time"]) && $args["time"] !== "i") {
					$expires = MathUtils::expirationStringToTimer($args[1]);
				}
				$expire = $expires === null ? "Not provided" : $expires->format("m-d-Y H:i");

				if(isset($args["reason"])) {
					$reason = implode(" ", array_slice($args, 2));
				} else {
					$reason = "Not provided";
				}
				$banList->addBan($user->getName(), $reason, $expires, $sender->getName());

				$player = Server::getInstance()->getPlayerByPrefix($user->getName());

				if($player instanceof CorePlayer) {
					$player->kick(Core::PREFIX . "You have been Banned By: " . $sender->getName() . "\n" . TextFormat::GRAY . "Reason: " . $reason . "\n" . TextFormat::GRAY . "Expires: " . $expire);
				}
				$sender->sendMessage(Core::PREFIX . "You have Banned " . $user->getName() . " for the Reason: " . $reason . ". Expires: " . $expire);
				Server::getInstance()->broadcastMessage(Core::PREFIX . $user->getName() . " has been Banned by " . $sender->getName() . " for the Reason: "  . $reason . ". Expires: " . $expire);
				return true;
			}
		});
	}
}