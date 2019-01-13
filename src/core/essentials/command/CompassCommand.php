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

class CompassCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("compass", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Command.Compass");
        $this->setUsage("[player]");
        $this->setDescription("Check what Direction you or a Player is Facing");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(isset($args[0])) {
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
				switch($player->getDirection()) {
					case 0:
						$direction = "South";
					break;
					case 1:
						$direction = "West";
					break;
					case 2:
						$direction = "North";
					break;
					case 3:
						$direction = "East";
					break;
					default:
						$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "There was an error while getting " . $player->getName() . "'s Direction");
						return false;
					break;
				}
				$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $player->getName() . " is Facing " . $direction);
                return true;
            }
        }
        if(!$sender instanceof GPPlayer) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
			switch($sender->getDirection()) {
				case 0:
					$direction = "South";
				break;
				case 1:
					$direction = "West";
				break;
				case 2:
					$direction = "North";
				break;
				case 3:
					$direction = "East";
				break;
				default:
					$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "There was an error while getting your Direction");
					return false;
				break;
			}
			$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "You are Facing " . $direction);
            return true;
        }
    }
}