<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use core\stats\Stats;

use pocketmine\command\{
	PluginCommand,
	CommandSender
};

class CurrencyChange extends PluginCommand {
	private $manager;

	public function __construct(Stats $manager) {
		parent::__construct("currencychange", Core::getInstance());

		$this->manager = $manager;

		$this->setPermission("core.stats.command.servers");
		$this->setAliases(["currencyc", "curc"]);
		$this->setDescription("Open the Currency change menu");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
		if(!$sender instanceof CorePlayer) {
			$sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
			return false;
		}
		if(!$sender->hasPermission($this->getPermission())) {
			$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
			return false;
		}
		if(!$sender instanceof CorePlayer) {
			$sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
			return false;
		} else {
			$sender->sendCurrencyChangeForm();
			$sender->sendMessage(Core::PREFIX . "Opened Currency Change menu");
			return true;
		}
	}
}