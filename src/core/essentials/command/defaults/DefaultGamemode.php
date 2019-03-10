<?php

namespace core\essentials\command\defaults;

use core\Core;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\Server;

class DefaultGamemode extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("defaultgamemode", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.defaultgamemode.command");
        $this->setUsage("<gamemode>");
        $this->setDescription("Set the Default Gamemode");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /defaultgamemode" . " " . $this->getUsage());
            return false;
        }
        if(Server::getGamemodeFromString($args[0]) === -1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Unknown Gamemode");
            return false;
        }
        if($this->core->getServer()->getDefaultGamemode() === $args[0]) {
            $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is already the Default Gamemode");
            return false;
        } else {
            $this->core->getServer()->setConfigInt("gamemode", $args[0]);
            $sender->sendMessage($this->core->getPrefix() . "Set Default Gamemode to " . $args[0]);
            return true;
        }
    }
}