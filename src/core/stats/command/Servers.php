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

class Servers extends PluginCommand {
    private $manager;
    
    public function __construct(Stats $manager) {
        parent::__construct("servers", Core::getInstance());
       
        $this->manager = $manager;
       
        $this->setPermission("core.stats.command.servers");
		$this->setAliases(["serverselector", "servermenu"]);
        $this->setDescription("Select the Server menu");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
            return false;
        } else {
            $sender->sendServerSelectorForm();
            $sender->sendMessage(Core::PREFIX . "Opened Servers menu");
            return true;
        }
    }
}