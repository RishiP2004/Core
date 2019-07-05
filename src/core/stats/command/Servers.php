<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Servers extends PluginCommand {
    private $core;
    
    public function __construct(Core $core) {
        parent::__construct("servers", $core);
       
        $this->core = $core;
       
        $this->setPermission("core.stats.command.servers");
		$this->setAliases(["serverselector", "servermenu"]);
        $this->setDescription("Select the Server menu");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
            $sender->sendServerSelectorForm();
            return true;
        }
    }
}