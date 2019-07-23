<?php

declare(strict_types = 1);

namespace core\anticheat\command\subCommand;

use core\Core;

use core\utils\SubCommand;

use pocketmine\command\{
	CommandSender,
	ConsoleCommandSender
};

class Report extends SubCommand {
	private $core;

	public function __construct(Core $core) {
		$this->core = $core;
	}

	public function canUse(CommandSender $sender) : bool {
		return $sender->hasPermission("core.cheat.subcommand.report");
	}

	public function getUsage() : string {
		return "<player> <cheat>";
	}

	public function getName() : string {
		return "report";
	}

	public function getDescription() : string {
		return "Report a Player for Cheating";
	}

	public function getAliases() : array {
		return [];
	}

	public function execute(CommandSender $sender, array $args) : bool {
		if(count($args) < 2) {
			return false;
		}
		$this->core->getStats()->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
				return false;
			} else {
				$cheat = $this->core->getAntiCheat()->getCheat(trim($args[1]));

				if(!$cheat instanceof \core\anticheat\cheat\Cheat) {
					$sender->sendMessage($this->core->getErrorPrefix() . $args[1] . " is not a valid Cheat");
					return false;
				} else {
					$this->core->getServer()->dispatchCommand(new ConsoleCommandSender(), "twitter dm GratonePix " . $user->getName() . " was Reported by " . $sender->getName() . " for " . $cheat->getName());
					$this->core->getServer()->dispatchCommand(new ConsoleCommandSender(), "discord " . $user->getName() . " was Reported by " . $sender->getName() . " for " . $cheat->getName());
					$sender->sendMessage($this->core->getPrefix() . "Thanks for Reporting " . $user->getName() . " for " . $cheat->getName());
					return true;
				}
			}
		});
		return false;
	}
}