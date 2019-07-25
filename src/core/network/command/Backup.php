<?php

declare(strict_types = 1);

namespace core\network\command;

use core\Core;

use core\network\BackThread;

use pocketmine\command\{
	PluginCommand,
	CommandSender
};

class Backup extends PluginCommand {
	private $core;

	public function __construct(Core $core) {
		parent::__construct("backup", $core);

		$this->core = $core;

		$this->setPermission("core.network.command.backup");
		$this->setUsage("[restore]");
		$this->setDescription("Backup the Server");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
		if(!$sender->hasPermission($this->getPermission())) {
			$sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
			return false;
		} else {
			if(isset($args[1]) && strtolower($args[1]) === "restore") {
				if(!$sender->hasPermission($this->getPermission() . ".restore")) {
					$sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use Restore the Server");
					return false;
				}
				$this->core->getNetwork()->restore();
				$sender->sendMessage($this->core->getPrefix() . "Restored the Server");
				$this->core->getServer()->broadcastMessage($this->core->getPrefix() . $sender->getName() . " Restored the Server. Restarting...");
				return true;
			}
			$this->core->getNetwork()->compress();
			$sender->sendMessage($this->core->getPrefix() . "Server backed up");
			$this->core->getServer()->broadcastMessage($this->core->getPrefix() . $sender->getName() . " has been Oped by " . $sender->getName());
			return true;
		}
	}
}