<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\event\entity\EntityDamageEvent;

class Kill extends PluginCommand {
    private $core;
    
    public function __construct(Core $core) {
        parent::__construct("kill", $core);
       
        $this->core = $core;

        $this->setPermission("core.essentials.defaults.kill.command");
        $this->setUsage("[player]");
        $this->setDescription("Kill yourself or Kill a Player");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }	
        if(isset($args[0])) {
            if(!$sender->hasPermission($this->getPermission() . ".other")) {
                $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
                return false;
            }
            $player = $this->core->getServer()->getPlayer($args[0]);

            if(!$player instanceof CorePlayer) {
                $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not Online");
                return false;           
            } else {
                $player->attack(new EntityDamageEvent($player, EntityDamageEvent::CAUSE_SUICIDE, 1000));
                $sender->sendMessage($this->core->getPrefix() . "Killed " . $player->getName());
                $player->sendMessage($this->core->getPrefix() . $sender->getName() . " Killed you");
                return true;
            }
        }
        if($sender instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
            $sender->attack(new EntityDamageEvent($sender, EntityDamageEvent::CAUSE_SUICIDE, 1000));
            $sender->sendMessage($this->core->getPrefix() . "You Killed yourself");
            return true;
        }
    }
}