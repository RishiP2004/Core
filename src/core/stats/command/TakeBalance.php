<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use core\stats\Statistics;

use pocketmine\command\{
	PluginCommand,
	CommandSender
};

class TakeBalance extends PluginCommand {
	private $core;

	public function __construct(Core $core) {
		parent::__construct("takebalance", $core);

		$this->core = $core;

		$this->setPermission("core.stats.command.takebalance");
		$this->setUsage("<player> <amount>");
		$this->setAliases(["takebal"]);
		$this->setDescription("Take Balance from a Player");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
		if(!$sender->hasPermission($this->getPermission())) {
			$sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
			return false;
		}
		if(count($args) < 2) {
			$sender->sendMessage($this->core->getErrorPrefix() . "Usage: /takebalance " . $this->getUsage());
			return false;
		}
		if(!is_numeric($args[1])) {
			$sender->sendMessage($this->core->getErrorPrefix() . $args[1] . " is not a valid Number");
			return false;
		}
		if(is_float($args[1])) {
			$sender->sendMessage($this->core->getErrorPrefix() . $args[1] . " must be an Integer");
			return false;
		}
		$this->core->getStats()->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
				return false;
			}
			if($user->getBalance() - $args[1] < 0) {
				$sender->sendMessage($this->core->getErrorPrefix() . $user->getName() . " will have less than 0 Balance");
				return false;
			} else {
				$user->setBalance($user->getBalance() - (int) $args[1]);

				$player = $this->core->getServer()->getPlayer($user->getName());

				if($player instanceof CorePlayer) {
					$player->sendMessage($this->core->getPrefix() . $sender->getName() . " Took " . Statistics::UNITS["balance"] . $args[1] . " from you");
				}
				$sender->sendMessage($this->core->getPrefix() . "Took away " . Statistics::UNITS["balance"] . $args[1] . " from " . $user->getName());
				return true;
			}
		});
		return false;
	}
}