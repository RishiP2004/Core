<?php

declare(strict_types = 1);

namespace core\anticheat\command\subCommand;

use core\Core;

use core\anticheat\cheat\Cheat;

use core\utils\SubCommand;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class History extends SubCommand {
	private $core;

	public function __construct(Core $core) {
		$this->core = $core;
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
		return "Edit the Cheat History of a Player";
	}

	public function getAliases() : array {
		return [];
	}

	public function execute(CommandSender $sender, array $args) : bool {
		if(count($args) < 3) {
			$sender->sendMessage($this->core->getErrorPrefix() . "Usage: " . $this->getUsage());
			return false;
		}
		$this->core->getStats()->getCoreUser($args[1], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
				return false;
			}
			$cheat = $this->core->getAntiCheat()->getCheat($args[2]);

			if(!$cheat instanceof Cheat and strtolower($args[2]) !== "all") {
				$sender->sendMessage($this->core->getErrorPrefix() . $args[2] . " is not a valid Cheat");
				return false;
			} else {
				switch(strtolower($args[0])) {
					case "add":
						if(isset($args[3])) {
							if(!is_int((int) $args[3])) {
								$sender->sendMessage($this->core->getErrorPrefix() . $args[3] . " is not a valid Number");
								return false;
							}
							$amount = $args[3];
						} else {
							$amount = 1;
						}
						$cheat->final($amount);
						$sender->sendMessage($this->core->getPrefix() . "Added " . $amount . " value to " . $user->getName() . "'s " . $cheat->getName() . " History");
						return true;
					break;
					case "remove":
						if(strtolower($args[2]) === "all") {
							foreach($this->core->getAntiCheat()->getCheats() as $cheat) {
								$user->setCheatHistory($cheat, 0);
								$sender->sendMessage($this->core->getPrefix() . "Reset " . $user->getName() . "'s Cheat History");
								return true;
							}
						}
						if(isset($args[3])) {
							if(!is_int((int) $args[3])) {
								$sender->sendMessage($this->core->getErrorPrefix() . $args[3] . " is not a valid Number");
								return false;
							}
							$amount = $args[3];
						} else {
							$amount = 1;
						}
						$user->subtractFromCheatHistory($this->core->getAntiCheat()->getCheat($cheat->getId()), $amount);
						$sender->sendMessage($this->core->getPrefix() . "Subtracted " . $args[0] . " value to " . $user->getName() . "'s " . $cheat->getName() . " History");
						return true;
					break;
					case "set":
						if(isset($args[3])) {
							if(!is_int((int) $args[3])) {
								$sender->sendMessage($this->core->getErrorPrefix() . $args[3] . " is not a valid Number");
								return false;
							}
							$amount = $args[3];
						}
						$cheat->final($args[3]);
						$user->setCheatHistory($this->core->getAntiCheat()->getCheat($cheat->getId()), $amount);
						$sender->sendMessage($this->core->getPrefix() . "Set " . $user->getName() . "'s " . $cheat->getId() . " History to " . $args[0]);
						return true;
					break;
					case "see":
						if(strtolower($args[2]) === "all") {
							$sender->sendMessage($this->core->getPrefix() . $user->getName() . "'s " . "Cheat History:");

							foreach($user->getCheatHistory as $history) {
								$cheat = $this->core->getAntiCheat()->getCheat($history[1]);
								$amount = $history[2];

								$sender->sendMessage(TextFormat::GRAY . $cheat->getName() . ": " . $amount);
							}
						} else {
							$sender->sendMessage($this->core->getPrefix() . $user->getName() . "'s " . $cheat->getName() . " Cheat History: " . $user->getCheatHistoryFor($cheat));
						}
					break;
				}
				return true;
			}
		});
		return false;
	}
}