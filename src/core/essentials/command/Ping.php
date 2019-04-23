<?php

declare(strict_types = 1);

namespace core\essentials\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Ping extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("ping", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.ping.command");
        $this->setUsage("[player]");
        $this->setDescription("Check yours or a Player's Ping");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(isset($args[0])) {
            if(!$sender->hasPermission($this->getPermission() . ".Other")) {
                $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
                return false;
            }
            $player = $this->core->getServer()->getPlayer($args[0]);

            if(!$player instanceof CorePlayer) {
                $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not Online");
                return false;
            }
            if(!$this->core->getStats()->getCoreUser($args[0])) {
                $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
                return false;
            } else {
                $sender->sendMessage($this->core->getPrefix() . $player->getName() . "'s Ping is: " . $player->getPing());
                return true;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
            $sender->sendMessage($this->core->getPrefix() . "Your Ping is: " . $sender->getPing());
            return true;
        }
    }
}