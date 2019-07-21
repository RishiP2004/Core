<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
	PluginCommand,
	CommandSender
};

class CurrencyChange extends PluginCommand {
	private $core;

	public function __construct(Core $core) {
		parent::__construct("currencychange", $core);

		$this->core = $core;

		$this->setPermission("core.stats.command.servers");
		$this->setAliases(["currencyc", "curc"]);
		$this->setDescription("Select the Server menu");
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
		if(!$sender instanceof CorePlayer) {
			$sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
			return false;
		} else {
			$sender->sendCurrencyChangeForm();
			$sender->sendMessage($this->core->getPrefix() . "Opened Currency Change menu");
			return true;
		}
	}
}