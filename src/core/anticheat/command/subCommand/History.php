<?php

declare(strict_types = 1);

namespace core\anticheat\command\subCommand;

use core\Core;

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
			if(!is_numeric($args[3])) {
				$sender->sendMessage($this->core->getErrorPrefix() . $args[3] . " is not Numeric");
				return false;
			}
			$cheat = $this->core->getAntiCheat()->getCheat(trim($args[2]));

			if(!$cheat instanceof \core\anticheat\cheat\Cheat and !strtolower($args[2]) !== "all" and strtolower($args[0]) !== "see") {
				$sender->sendMessage($this->core->getErrorPrefix() . $args[2] . " is not a valid Cheat");
				return false;
			} else {
				switch(strtolower($args[0])) {
					case "add":
						$user->addToCheatHistory($this->core->getAntiCheat()->getCheat($cheat->getId()), $args[3]);
						$sender->sendMessage($this->core->getPrefix() . "Added " . $args[0] . " value to " . $user->getName() . "'s " . $cheat->getName() . " History");
					break;
					case "remove":
						$user->subtractFromCheatHistory($this->core->getAntiCheat()->getCheat($cheat->getId()), $args[3]);
						$sender->sendMessage($this->core->getPrefix() . "Subtracted " . $args[0] . " value to " . $user->getName() . "'s " . $cheat->getName() . " History");
					break;
					case "set":
						$user->setCheatHistory($this->core->getAntiCheat()->getCheat($cheat->getId()), $args[3]);
						$sender->sendMessage($this->core->getPrefix() . "Set " . $user->getName() . "'s " . $cheat->getId() . " History to " . $args[0]);
					break;
					case "see":
						if(isset($args[2]) === "all") {
							$sender->sendMessage($this->core->getPrefix() . $user->getName() . "'s " . "Cheat History:");

							foreach($user->getCheatHistory as $history) {
								$cheat = $this->core->getAntiCheat()->getCheat($history[1]);
								$amount = $history[2];

								$sender->sendMessage(TextFormat::GRAY . $cheat->getName() . ": " . $amount);
							}
						} else {
							$sender->sendMessage($this->core->getPrefix() . $user->getName() . "'s " . " Cheat History:");
							$sender->sendMessage();
						}
					break;
				}
				return true;
			}
		});
		return false;
	}
}