<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use core\essentials\Essentials;

use pocketmine\Server;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\event\entity\EntityDamageEvent;

class Kill extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
		parent::__construct("kill", Core::getInstance());
       
        $this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.kill");
        $this->setUsage("[player]");
        $this->setDescription("Kill yourself or Kill a Player");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }	
        if(isset($args[0])) {
            if(!$sender->hasPermission($this->getPermission() . ".other")) {
                $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
                return false;
            }
            $player = Server::getInstance()->getPlayer($args[0]);

            if(!$player instanceof CorePlayer) {
                $sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not Online");
                return false;           
            } else {
                $player->attack(new EntityDamageEvent($player, EntityDamageEvent::CAUSE_SUICIDE, 1000));
                $sender->sendMessage(Core::PREFIX . "Killed " . $player->getName());
                $player->sendMessage(Core::PREFIX . $sender->getName() . " Killed you");
                return true;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
			$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /kill " . $this->getUsage());
            return false;
        } else {
            $sender->attack(new EntityDamageEvent($sender, EntityDamageEvent::CAUSE_SUICIDE, 1000));
            $sender->sendMessage(Core::PREFIX . "You Killed yourself");
            return true;
        }
    }
}