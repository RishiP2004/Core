<?php
/**
 *    ___________________________
 *   /  _____/\______   \_   ___ \  ___________   ____
 *  /   \  ___ |     ___/    \  \/ /  _ \_  __ \_/ __ \
 *  \    \_\  \|    |   \     \___(  <_> )  | \/\  ___/
 *   \______  /|____|    \______  /\____/|__|    \___  >
 *          \/                  \/                   \/
 */
namespace GPCore\Essentials\Defaults\Commands;

use GPCore\GPCore;

use GPCore\Stats\Objects\GPPlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\event\entity\EntityDamageEvent;

class KillCommand extends PluginCommand {
    private $GPCore;
    
    public function __construct(GPCore $GPCore) {
        parent::__construct("kill", $GPCore);
       
        $this->GPCore = $GPCore;
       
		$this->setAliases(["kill"]);
        $this->setPermission("GPCore.Essentials.Defaults.Command.Kill");
        $this->setUsage("[player]");
        $this->setDescription("Kill yourself or Kill a Player");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender instanceof GPPlayer) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        }
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }	
        if(isset($args[0])) {
            if(!$sender->hasPermission($this->getPermission() . ".Other")) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
                return false;
            }
            $user = $this->GPCore->getStats()->getGPUser($args[0]);

            if(!$user->hasAccount()) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Player");
                return false;
            }
            $player = $user->getGPPlayer();

            if(!$player instanceof GPPlayer) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $user->getUsername() . " is not Online");
                return false;
            } else {
                $sender->getServer()->getPluginManager()->callEvent($event = new EntityDamageEvent($args[0], EntityDamageEvent::CAUSE_SUICIDE, 1000));

                if(!$event->isCancelled()) {
                    return false;
                }
                $player->setLastDamageCause($event);
                $player->setHealth(0);
                $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Killed " . $user->getUsername());
                $player->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . " Killed you");
                return true;
            }
        }
        if($sender instanceof GPPlayer) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
			$sender->getServer()->getPluginManager()->callEvent($event = new EntityDamageEvent($sender, EntityDamageEvent::CAUSE_SUICIDE, 1000));

			if(!$event->isCancelled()) {
				return false;
			}
			$sender->setLastDamageCause($event);
			$sender->setHealth(0);
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "You Killed yourself");
            return true;
        }
    }
}