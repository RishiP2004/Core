<?php
/**
 *    ___________________________
 *   /  _____/\______   \_   ___ \  ___________   ____
 *  /   \  ___ |     ___/    \  \/ /  _ \_  __ \_/ __ \
 *  \    \_\  \|    |   \     \___(  <_> )  | \/\  ___/
 *   \______  /|____|    \______  /\____/|__|    \___  >
 *          \/                  \/                   \/
 */
namespace GPCore\Essentials\Commands;

use GPCore\GPCore;

use GPCore\Stats\Objects\GPPlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class WorldCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("world", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Command.World");
        $this->setUsage("<world> [player]");
        $this->setDescription("Teleport yourself or a Player to a World");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /world" . " " . $this->getUsage());
            return false;
        }
		if(!$sender->getServer()->isLevelGenerated($args[0])){
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "World doesn't exist");
            return false;
		}
		if(!$sender->getServer()->isLevelLoaded($args[0])) {
			$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Level is not loaded yet. Loading...");
			
			if(!$sender->getServer()->loadLevel($args[0])) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "The level couldn't be loaded");
                return false;
            }
		}
        if(isset($args[1])) {
            if(!$sender->hasPermission($this->getPermission() . ".Other")) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
                return false;
            }
            $player = $this->GPCore->getServer()->getPlayer($args[0]);

            if(!$player instanceof GPPlayer) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not Online");
                return false;
            }
            if(!$player->getGPUser()->hasAccount()) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Player");
                return false;
            } else {
                $player->teleport($this->GPCore->getServer()->getLevelByName($args[0])->getSpawnLocation(), 0, 0);
                $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Teleported " . $player->getName() . " to the World: " . $args[0]);
                $player->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . " Teleported you to the World: " . $args[0]);
                return true;
            }
        }
        if(!$sender instanceof GPPlayer) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
			$sender->teleport($this->GPCore->getServer()->getLevelByName($args[0])->getSpawnLocation(), 0, 0);
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Teleported to the World: " . $args[0]);
            return true;
        }
    }
}