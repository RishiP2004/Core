<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;

use core\stats\Stats;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class Accounts extends PluginCommand {
    private $manager;

    public function __construct(Stats $manager) {
        parent::__construct("accounts", Core::getInstance());

        $this->manager = $manager;

        $this->setAliases(["accs"]);
        $this->setPermission("core.stats.command.accounts");
        $this->setDescription("Get all registered Accounts");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        } else {
			$this->manager->getAllCoreUsers(function($users) use($sender) {
				$sender->sendMessage(Core::PREFIX . "Total Accounts Registered (x" . count($users) . ")");
				
				$allUsers = [];
				
				foreach($users as $user) {
					$allUsers[] = $user->getName();
				}
				$sender->sendMessage(TextFormat::GRAY . implode(", ", $allUsers));
			});
			return true;
		}
    }
}