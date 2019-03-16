<?php

namespace core\essentials\command\defaults;

use core\Core;

use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Op extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("deop", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.op.command");
        $this->setUsage("<player>");
        $this->setDescription("Op a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /op" . " " . $this->getUsage());
            return false;
        }
        if(!$user = $this->core->getStats()->getCoreUser($args[1])) {
            $sender->sendMessage($this->core->getErrorPrefix() . $args[1] . " is not a valid Player");
            return false;
        }
        $player = $sender->getServer()->getOfflinePlayer($user->getName());

        if(!$player->isOp()) {
            $sender->sendMessage($this->core->getErrorPrefix() . $user->getName() . " is not Op");
            return false;
        } else {
            $player->setOp(true);

            if($player instanceof CorePlayer) {
                $player->sendMessage($this->core->getPrefix() . $sender->getName() . " Oped you");
            }
            $sender->sendMessage($this->core->getPrefix() . $user->getName() . " is now Op");
            $this->core->getServer()->broadcastMessage($this->core->getPrefix() . $user->getName() . " has been Oped by " . $sender->getName());
            return true;
        }
    }
}