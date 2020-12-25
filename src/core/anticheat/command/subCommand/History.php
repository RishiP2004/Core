<?php

declare(strict_types = 1);

namespace core\anticheat\command\subCommand;

use core\Core;

use core\anticheat\AntiCheat;

use core\stats\Stats;

use core\utils\SubCommand;

use core\anticheat\cheat\Cheat;

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class History extends SubCommand {
	private $manager;

	public function __construct(AntiCheat $manager) {
		$this->manager = $manager;
	}

	public function canUse(CommandSender $sender) : bool {
		return $sender->hasPermission("core.cheat.subcommand.history");
	}

	public function getUsage() : string {
		return "<add : remove : set : see> <player> <cheat : all> [amount]";
	}

	public function getName() : string {
		return "history";
	}

	public function getDescription() : string {
		return "Edit the Cheats History of a Player";
	}

	public function getAliases() : array {
		return [];
	}

	public function execute(CommandSender $sender, array $args) : bool {
		if(count($args) < 3) {
			$sender->sendMessage(Core::ERROR_PREFIX . "Usage: " . $this->getUsage());
			return false;
		}
		Stats::getInstance()->getCoreUser($args[1], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
				return false;
			}
			$cheat = $this->manager->getCheat($args[2]);

			if(!$cheat instanceof Cheat and strtolower($args[2]) !== "all") {
				$sender->sendMessage(Core::ERROR_PREFIX . $args[2] . " is not a valid Cheats");
				return false;
			} else {
				switch(strtolower($args[0])) {
					case "add":
						if(isset($args[3])) {
							if(!is_int((int) $args[3])) {
								$sender->sendMessage(Core::ERROR_PREFIX . $args[3] . " is not a valid Number");
								return false;
							}
							$amount = $args[3];
						} else {
							$amount = 1;
						}
						$cheat->final($amount);
						$sender->sendMessage(Core::PREFIX . "Added " . $amount . " value to " . $user->getName() . "'s " . $cheat->getName() . " History");
						return true;
					break;
					case "remove":
						if(strtolower($args[2]) === "all") {
							foreach($this->manager->getCheats() as $cheat) {
								$user->setCheatHistory($cheat, 0);
								$sender->sendMessage(Core::PREFIX . "Reset " . $user->getName() . "'s Cheats History");
								return true;
							}
						}
						if(isset($args[3])) {
							if(!is_int((int) $args[3])) {
								$sender->sendMessage(Core::ERROR_PREFIX . $args[3] . " is not a valid Number");
								return false;
							}
							$amount = $args[3];
						} else {
							$amount = 1;
						}
						$user->subtractFromCheatHistory($this->manager->getCheat($cheat->getId()), $amount);
						$sender->sendMessage(Core::PREFIX . "Subtracted " . $args[0] . " value to " . $user->getName() . "'s " . $cheat->getName() . " History");
						return true;
					break;
					case "set":
						if(isset($args[3])) {
							if(!is_int((int) $args[3])) {
								$sender->sendMessage(Core::ERROR_PREFIX . $args[3] . " is not a valid Number");
								return false;
							}
							$amount = $args[3];
						}
						$cheat->final($args[3]);
						$user->setCheatHistory($this->manager->getCheat($cheat->getId()), $amount);
						$sender->sendMessage(Core::PREFIX . "Set " . $user->getName() . "'s " . $cheat->getId() . " History to " . $args[0]);
						return true;
					break;
					case "see":
						if(strtolower($args[2]) === "all") {
							$sender->sendMessage(Core::PREFIX . $user->getName() . "'s " . "Cheats History:");

							foreach($user->getCheatHistory() as $history) {
								$cheat = $this->manager->getCheat($history[1]);
								$amount = $history[2];

								$sender->sendMessage(TextFormat::GRAY . $cheat->getName() . ": " . $amount);
							}
						} else {
							$sender->sendMessage(Core::PREFIX . $user->getName() . "'s " . $cheat->getName() . " Cheats History: " . $user->getCheatHistoryFor($cheat));
						}
					break;
				}
				return true;
			}
		});
		return false;
	}
}