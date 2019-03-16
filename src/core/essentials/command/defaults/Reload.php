<?php

namespace core\essentials\command\defaults;

use core\Core;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Reload extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("reload", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.reload.command");
        $this->setDescription("Reload the Server");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        } else {
            $sender->sendMessage($this->core->getPrefix() . "Reloading the Server...");
            $this->core->getServer()->broadcastMessage($this->core->getPrefix() . $sender->getName() . " is Reloading the Server...");
            $this->core->getServer()->reload();
            $sender->sendMessage($this->core->getPrefix() . "Reloaded the Server");
            $this->core->getServer()->broadcastMessage($this->core->getPrefix() . $sender->getName() . " Reloaded the Server");
            return true;
        }
    }
}