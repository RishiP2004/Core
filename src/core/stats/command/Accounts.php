<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CoreUser;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class Accounts extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("accounts", $core);

        $this->core = $core;

        $this->setAliases(["accs"]);
        $this->setPermission("core.stats.command.accounts");
        $this->setDescription("Get all registered Accounts");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        } else {
			$sender->sendMessage($this->core->getPrefix() . "Total Accounts Registered (x" . count($this->core->getStats()->getCoreUsers()) . ")");
		
			$users = [];
		
			foreach($this->core->getStats()->getCoreUsers() as $user) {
			    if($user instanceof CoreUser) {
                    $users[] = $user->getName();
                }
			}
			$sender->sendMessage(TextFormat::GRAY . implode(", ", $users));
			return true;
		}
    }
}