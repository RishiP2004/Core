<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Stop extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("stop", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.stop.command");
        $this->setDescription("Stop the Server");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        } else {
            $this->core->getServer()->shutdown();
            $sender->sendMessage($this->core->getPrefix() . "Stopped the Server");
            $this->core->getServer()->broadcastMessage($this->core->getPrefix() . $sender->getName() . " Stopped the Server");
            return true;
        }
    }
}