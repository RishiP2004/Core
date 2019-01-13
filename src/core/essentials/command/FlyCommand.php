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

class FlyCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("fly", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Command.Fly");
        $this->setUsage("[value] [player]");
        $this->setDescription("Set yours or a Player's Fly mode");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
		$value = false;
		
		if(isset($args[0])) {
			switch(strtolower($args[0])) {
				case "true":
				case "on":
					$value = true;
				break;
				case "false":
				case "off":
					$value = false;
				break;	
				default:
					$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $value . " is not a valid Boolean");
				break;
			}
		}
        if(isset($args[1])) {
            if(!$sender->hasPermission($this->getPermission() . ".Other")) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
                return false;
            }
            $player = $this->GPCore->getServer()->getPlayer($args[1]);

            if(!$player instanceof GPPlayer) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[1] . " is not Online");
                return false;
            }
            if(!$player->getGPUser()->hasAccount()) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[1] . " is not a valid Player");
                return false;
            } else {
				if(isset($args[0])) {
					$flying = $value;
				} else {
					$flying = $player->getAllowFlight() === false ? true : false;
				}
				$player->setAllowFlight($flying);
				$player->setFlying($flying);
				$player->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . " set your Fly mode to " . strtoupper($flying));
				$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Set " . $player->getName() . "'s Fly mode to " . strtoupper($flying));
				return true;
            }
        }
        if(!$sender instanceof GPPlayer) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
			if(isset($args[0])) {
				$flying = $value;
			} else {
				$flying = $sender->getAllowFlight() === false ? true : false;
			}
			$sender->setAllowFlight($flying);
			$sender->setFlying($flying);
			$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Set your Fly mode to " . strtoupper($flying));
			return true;
        }
    }
}