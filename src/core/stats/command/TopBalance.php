<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;

use core\stats\Stats;

use core\essentials\Essentials;

use pocketmine\Server;

use pocketmine\command\{
	PluginCommand,
	CommandSender
};

class TopBalance extends PluginCommand {
	private $manager;

	public function __construct(Stats $manager) {
		parent::__construct("topbalance", Core::getInstance());

		$this->manager = $manager;

		$this->setPermission("core.stats.command.topbalance");
		$this->setUsage("[page]");
		$this->setDescription("Check the Top Balance");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
		if(!$sender->hasPermission($this->getPermission())) {
			$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
			return false;
		} else {
			$page = $args[0] ?? 1;
			$banned = [];
			$ops = [];
			
			if(!empty(Essentials::getInstance()->getNameBans()->getEntries())) {
				foreach(Essentials::getInstance()->getNameBans()->getEntries() as $entry) {
					$this->manager->getCoreUser($entry->getName(), function($user) use ($banned) {
						if(!is_null($user)) {
							$banned[] = $user;
						}
					});
				}
			}
			if(!empty(Essentials::getInstance()->getIpBans()->getEntries())) {
				foreach(Essentials::getInstance()->getIpBans()->getEntries() as $entry) {
					$this->manager->getCoreUser($entry->getName(), function($user) use ($banned) {
						if(!is_null($user)) {
							$banned[] = $user;
						}
					});
				}
			}
			if(!empty(Server::getInstance()->getOps()->getAll())) {
				foreach(Server::getInstance()->getOps()->getAll() as $op) {
					$this->manager->getCoreUser((string) $op, function($user) use ($banned) {
						if(!is_null($user)) {
							$ops[] = $user;
						}
					});
				}
			}
			$this->manager->sendTopEconomy("balance", $sender, (int) $page, $ops, $banned);
			return true;
		}
	}
}