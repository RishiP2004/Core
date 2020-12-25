<?php

declare(strict_types = 1);

namespace core\anticheat\command\subCommand;

use core\Core;

use core\anticheat\AntiCheat;
use core\anticheat\cheat\Cheat;

use core\stats\Stats;

use core\social\Social;

use core\utils\SubCommand;

use pocketmine\Server;

use pocketmine\command\{
	CommandSender,
	ConsoleCommandSender
};

class Report extends SubCommand {
	private $manager;

	public function __construct(AntiCheat $manager) {
		$this->manager = $manager;
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
		Stats::getInstance()->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
				return false;
			} else {
				$cheat = $this->manager->getCheat(trim($args[1]));

				if(!$cheat instanceof Cheat) {
					$sender->sendMessage(Core::ERROR_PREFIX . $args[1] . " is not a valid Cheats");
					return false;
				} else {
					if(empty(Social::KEY && Social::SECRET && Social::TOKEN && Social::TOKEN_SECRET)) {
						Server::getInstance()->dispatchCommand(new ConsoleCommandSender(), "twitter dm GratonePix " . $user->getName() . " was Reported by " . $sender->getName() . " for " . $cheat->getName());
					}
					if(empty(Social::WEB_HOOK_URL)) {
						Server::getInstance()->dispatchCommand(new ConsoleCommandSender(), "discord " . $user->getName() . " was Reported by " . $sender->getName() . " for " . $cheat->getName());
					}
					Server::getInstance()->dispatchCommand(new ConsoleCommandSender(), "chat say staff " . $user->getName() . " was Reported by " . $sender->getName() . " for " . $cheat->getName());
					$sender->sendMessage(Core::PREFIX . "Thanks for Reporting " . $user->getName() . " for " . $cheat->getName());
					return true;
				}
			}
		});
		return false;
	}
}