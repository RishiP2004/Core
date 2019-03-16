<?php

namespace core\essentials\command\defaults;

use core\Core;

use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class SetSpawn extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("setspawn", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.setspawn.command");
        $this->setUsage("[player]");
        $this->setDescription("Set the Spawn");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(count($args) < 1) {
            if(!$sender instanceof CorePlayer) {
                $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
                return false;
            }
        }
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        } else {
            $sender->getLevel()->setSpawnLocation($sender);
            $sender->getServer()->setDefaultLevel($sender->getLevel());
            $sender->sendMessage($this->core->getPrefix() . "Server's Spawn changed to Level: " . $sender->getLevel()->getName() . " at X: " . $sender->getX() . ", Y: " . $sender->getY() . ", Z: " . $sender->getZ());
            $this->core->getServer()->broadcastMessage($this->core->getPrefix() . "Server's Spawn changed to Level: " . $sender->getLevel()->getName() . " at X: " . $sender->getX() . ", Y: " . $sender->getY() . ", Z: " . $sender->getZ() . " by " . $sender->getName());
            return true;
        }
    }
}