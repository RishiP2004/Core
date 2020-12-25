<?php

declare(strict_types = 1);

namespace core\network\command;

use core\Core;

use core\network\Network;

use pocketmine\command\{
	PluginCommand,
	CommandSender
};
use pocketmine\Server;

class Backup extends PluginCommand {
	private $manager;

	public function __construct(Network $manager) {
		parent::__construct("backup", Core::getInstance());

		$this->manager = $manager;

		$this->setPermission("core.network.command.backup");
		$this->setUsage("[restore]");
		$this->setDescription("Compress the Server");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
		if(!$sender->hasPermission($this->getPermission())) {
			$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
			return false;
		} else {
			if(isset($args[1]) && strtolower($args[1]) === "restore") {
				if(!$sender->hasPermission($this->getPermission() . ".restore")) {
					$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use Restore the Server");
					return false;
				}
				$this->manager->restore();
				$sender->sendMessage(Core::PREFIX . "Restored the Server");
				Server::getInstance()->broadcastMessage(Core::PREFIX . $sender->getName() . " Restored the Server. Restarting...");
				return true;
			}
			$this->manager->compress();
			$sender->sendMessage(Core::PREFIX . "Server backed up");
			Server::getInstance()->broadcastMessage(Core::PREFIX . $sender->getName() . " has been Oped by " . $sender->getName());
			return true;
		}
	}
}