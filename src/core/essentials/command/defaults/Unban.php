<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Unban extends PluginCommand {
	private $core;

    public function __construct(Core $core) {
        parent::__construct("unban", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.unban.command");
        $this->setUsage("<player>");
        $this->setDescription("Unban a Player");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /unban" . " " . $this->getUsage());
            return false;
        }
        if(!$user = $this->core->getStats()->getCoreUser($args[0])) {
            $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
            return false;
        }
        $banList = $this->core->getEssentials()->getNameBans();

        if(!$banList->isBanned($user->getName())) {
            $sender->sendMessage($this->core->getErrorPrefix() . $user->getName() . " is not Banned");
            return false;
        } else {
            $banList->remove($user->getName());

            $player = $this->core->getServer()->getPlayer($user->getName());

            if($player instanceof CorePlayer) {
                $player->sendMessage($this->core->getPrefix() . "You have been Unbanned By: " . $sender->getName());
            }
            $sender->sendMessage($this->core->getPrefix() . "You have Unbanned " . $user->getName());
            $this->core->getServer()->broadcastMessage($this->core->getPrefix() . $user->getName() . " has been Unbanned by " . $sender->getName());
            return true;
        }
    }
}
