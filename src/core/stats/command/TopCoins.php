<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class TopCoins extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("topcoins", $core);

        $this->core = $core;

        $this->setPermission("core.stats.command.topcoins");
        $this->setUsage("[page]");
        $this->setDescription("Check the Top Coins");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        } else {
			$page = $args[0] ?? 1;
			$banned = [];
			$ops = [];
			
			if(!empty($this->core->getServer()->getNameBans()->getEntries())) {
				foreach($this->core->getServer()->getNameBans()->getEntries() as $entry) {
					$this->core->getStats()->getCoreUser($entry->getName(), function($user) use ($banned) {
						if(!is_null($user)) {
							$banned[] = $user;
						}
					});
				}
			}
			if(!empty($this->core->getServer()->getOps()->getAll())) {
				foreach($this->core->getServer()->getOps()->getAll() as $op) {
					$this->core->getStats()->getCoreUser((string) $op, function($user) use ($banned) {
						if(!is_null($user)) {
							$ops[] = $user;
						}
					});
				}
			}
            $this->core->getStats()->sendTopEconomy("coins", $sender, $page, $ops, $banned);
            return true;
        }
    }
}