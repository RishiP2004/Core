<?php

declare(strict_types = 1);

namespace core\anticheat\command\subCommand;

use core\Core;
use core\anticheat\command\args\CheatArgument;
use core\player\traits\PlayerCallTrait;
use core\player\command\args\OfflinePlayerArgument;

use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\args\{
	IntegerArgument,
	RawStringArgument
};

use core\anticheat\AntiCheatManager;

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class HistorySubCommand extends BaseSubCommand {
	use PlayerCallTrait;

	public function prepare() : void {
		$this->setPermission("cheat.subcommand.history");
		$this->registerArgument(0, new RawStringArgument("type"));
		$this->registerArgument(1, new OfflinePlayerArgument("player"));
		$this->registerArgument(2, new CheatArgument("cheat"));
		$this->registerArgument(2, new IntegerArgument("amount", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$this->getCoreUser($args["player"]->getName(), function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args["player"]->getName() . " is not a valid Player");
				return false;
			}
			switch(strtolower($args["type"])) {
				case "add":
					if(isset($args["amount"])) {
						if(!is_int((int) $args["amount"])) {
							$sender->sendMessage(Core::ERROR_PREFIX . $args["amount"] . " is not a valid Number");
							return false;
						}
						$amount = $args["amount"];
					} else {
						$amount = 1;
					}
					$args["cheat"]->final($amount);
					$sender->sendMessage(Core::PREFIX . "Added " . $amount . " value to " . $user->getName() . "'s " . $args["cheat"]->getName() . " History");
					return true;
				break;
				case "remove":
					if(strtolower($args["cheat"]) === "all") {
						foreach(AntiCheatManager::getInstance()->getCheats() as $cheat) {
							$user->setCheatHistory($cheat, 0);
							$sender->sendMessage(Core::PREFIX . "Reset " . $user->getName() . "'s Cheats History");
							return false;
						}
					}
					if(isset($args["amount"])) {
						if(!is_int((int) $args["amount"])) {
							$sender->sendMessage(Core::ERROR_PREFIX . $args[3] . " is not a valid Number");
							return false;
						}
						$amount = $args["amount"];
					} else {
						$amount = 1;
					}
					$user->subtractFromCheatHistory($args["cheat"], $amount);
					$sender->sendMessage(Core::PREFIX . "Subtracted " . $args[0] . " value to " . $user->getName() . "'s " . $args["cheat"]->getName() . " History");
					return true;
				break;
				case "set":
					if(isset($args["amount"])) {
						if(!is_int((int) $args["amount"])) {
							$sender->sendMessage(Core::ERROR_PREFIX . $args["amount"] . " is not a valid Number");
							return false;
						}
						$amount = $args["amount"];
					}
					$args["cheat"]->final($args["amount"]);
					$user->setCheatHistory($args["cheat"], $amount);
					$sender->sendMessage(Core::PREFIX . "Set " . $user->getName() . "'s " . $args["cheat"] . " History to " . $args[0]);
					return true;
				break;
				case "see":
					if(strtolower($args["cheat"]) === "all") {
						$sender->sendMessage(Core::PREFIX . $user->getName() . "'s " . "Cheats History:");

						foreach($user->getCheatHistory() as $history) {
							$cheat = AntiCheatManager::getInstance()->getCheat($history[1]);
							$amount = $history[2];
							$sender->sendMessage(TextFormat::GRAY . $cheat->getName() . ": " . $amount);
						}
					} else {
						$sender->sendMessage(Core::PREFIX . $user->getName() . "'s " . $args["cheat"]->getName() . " Cheats History: " . $user->getCheatHistoryFor($args["cheat"]));
					}
				break;
			}
			return true;
		});
	}
}