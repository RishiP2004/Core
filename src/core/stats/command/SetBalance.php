<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use core\stats\{
	Stats,
	Statistics
};

use pocketmine\Server;

use pocketmine\command\{
	PluginCommand,
	CommandSender
};

class SetBalance extends PluginCommand {
	private $manager;

	public function __construct(Stats $manager) {
		parent::__construct("setbalance", Core::getInstance());

		$this->manager = $manager;

		$this->setPermission("core.stats.command.setbalance");
		$this->setUsage("<player> <amount>");
		$this->setAliases(["setbal"]);
		$this->setDescription("Set a Player's Balance");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
		if(!$sender->hasPermission($this->getPermission())) {
			$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
			return false;
		}
		if(count($args) < 2) {
			$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /setbalance " . $this->getUsage());
			return false;
		}
		if(!is_numeric($args[1])) {
			$sender->sendMessage(Core::ERROR_PREFIX . $args[1] . " is not a valid Number");
			return false;
		}
		if(is_float($args[1])) {
			$sender->sendMessage(Core::ERROR_PREFIX . $args[1] . " must be an Integer");
			return false;
		}
		$this->manager->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
				return false;
			}
			if($args[1] > Statistics::MAXIMUMS["balance"]) {
				$sender->sendMessage(Core::ERROR_PREFIX . $user->getName() . " will have over the Maximum amount of Balance");
				return false;
			} else {
				$user->setBalance((int) $args[1]);

				$player = Server::getInstance()->getPlayer($user->getName());

				if($player instanceof CorePlayer) {
					$player->sendMessage(Core::PREFIX . $sender->getName() . " set your Balance to " . Statistics::UNITS["balance"] . $args[1]);
				}
				$sender->sendMessage(Core::PREFIX . "Set " . $user->getName() . "'s Balance to " . Statistics::UNITS["balance"] . $args[1]);
				return true;
			}
		});
		return false;
	}
}