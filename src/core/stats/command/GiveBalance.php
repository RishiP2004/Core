<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
	PluginCommand,
	CommandSender
};

class GiveBalance extends PluginCommand {
	private $core;

	public function __construct(Core $core) {
		parent::__construct("givebalance", $core);

		$this->core = $core;

		$this->setPermission("core.stats.command.givebalance");
		$this->setUsage("<player> <amount>");
		$this->setAliases(["givebal"]);
		$this->setDescription("Give Balance to a Player");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
		if(!$sender->hasPermission($this->getPermission())) {
			$sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
			return false;
		}
		if(count($args) < 2) {
			$sender->sendMessage($this->core->getErrorPrefix() . "Usage: /givebalance" . " " . $this->getUsage());
			return false;
		}
		$user = $this->core->getStats()->getCoreUser($args[0]);

		if(!$user) {
			$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
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
		if($user->getBalance() + $args[1] > $this->core->getStats()->getMaximumEconomy("balance")) {
			$sender->sendMessage($this->core->getErrorPrefix() . $user->getName() . " will have over the Maximum amount of Balance");
			return false;
		} else {
			$user->setBalance($user->getBalance() - $args[1]);

			$player = $this->core->getServer()->getPlayer($user->getName());

			if($player instanceof CorePlayer) {
				$player->sendMessage($this->core->getPrefix() . $sender->getName() . " Gave you " . $this->core->getStats()->getEconomyUnit("balance") . $args[1]);
			}
			$sender->sendMessage($this->core->getPrefix() . "Gave " . $user->getName() . " " . $this->core->getStats()->getEconomyUnit("balance") . $args[1]);
			return true;
		}
	}
}