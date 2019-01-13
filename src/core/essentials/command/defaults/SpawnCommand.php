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

use pocketmine\level\Location;

class SpawnCommand extends PluginCommand {
    private $GPCore;
    
    public function __construct(GPCore $GPCore) {
        parent::__construct("spawn", $GPCore);
       
        $this->GPCore = $GPCore;
       
        $this->setPermission("GPCore.Essentials.Defaults.Command.Spawn");
        $this->setUsage("[player]");
        $this->setDescription("Go to Spawn or send a Player there");
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
                $sender->sendMessage($this->GPCore->getInstance()->getBroadcast()->getErrorPrefix() . $user->getUsername() . " is not Online");
                return false;
            } else {
                $player->teleport(Location::fromObject($this->GPCore->getServer()->getDefaultLevel()->getSpawnLocation(), $this->GPCore->getServer()->getDefaultLevel()));
				$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Teleported " . $user->getUsername() . " to Spawn");
				$player->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . " Teleported you to Spawn");
                return true;
            }
        }
        if(!$sender instanceof GPPlayer) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
			$sender->teleport(Location::fromObject($this->GPCore->getServer()->getDefaultLevel()->getSpawnLocation(), $this->GPCore->getServer()->getDefaultLevel()));
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Teleported to Spawn");
            return true;
        }
    }
}