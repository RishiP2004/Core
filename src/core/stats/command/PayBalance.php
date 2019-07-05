<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
	PluginCommand,
	CommandSender
};

class PayBalance extends PluginCommand {
	private $core;

	public function __construct(Core $core) {
		parent::__construct("payabalance", $core);

		$this->core = $core;

		$this->setPermission("core.stats.command.paybalance");
		$this->setUsage("<player> <amount>");
		$this->setAliases(["paybal"]);
		$this->setDescription("Pay a Player Balance");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
		if(!$sender instanceof CorePlayer) {
			$sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
			return false;
		}
		if(!$sender->hasPermission($this->getPermission())) {
			$sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
			return false;
		}
		if(count($args) < 2) {
			$sender->sendMessage($this->core->getErrorPrefix() . "Usage: /paybalance" . " " . $this->getUsage());
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
			if($user->getBalance() + $args[1] > $this->core->getStats()->getMaximumEconomy("balance")) {
				$sender->sendMessage($this->core->getErrorPrefix() . $user->getName() . " will have over the Maximum amount of Balance");
				return false;
			}
			if($sender->getCoreUser()->getBalance() < $args[1]) {
				$sender->sendMessage($this->core->getErrorPrefix() . "You do not have enough Balance");
				return false;
			} else {
				$user->setBalance($user->getBalance() + (int) $args[1]);
				$sender->getCoreUser()->setBalance($sender->getCoreUser()->getBalance() - $args[1]);

				$player = $this->core->getServer()->getPlayer($user->getName());

				if($player instanceof CorePlayer) {
					$player->sendMessage($this->core->getPrefix() . $sender->getName() . " Paid you " . $this->core->getStats()->getEconomyUnit("balance") . $args[1]);
				}
				$sender->sendMessage($this->core->getPrefix() . "Paid " . $user->getName() . " " . $this->core->getStats()->getEconomyUnit("balance") . $args[1]);
				return true;
			}
		});
		return false;
	}
}