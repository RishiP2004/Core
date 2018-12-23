<?php
/**
 *    ___________________________
 *   /  _____/\______   \_   ___ \  ___________   ____
 *  /   \  ___ |     ___/    \  \/ /  _ \_  __ \_/ __ \
 *  \    \_\  \|    |   \     \___(  <_> )  | \/\  ___/
 *   \______  /|____|    \______  /\____/|__|    \___  >
 *          \/                  \/                   \/
 */
namespace GPCore\Stats\Commands;

use GPCore\GPCore;

use GPCore\Stats\Objects\GPPlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class RemovePlayerPermissionCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("removeplayerpermission", $GPCore);

        $this->GPCore = $GPCore;

        $this->setAliases(["removepperm"]);
        $this->setPermission("GPCore.Stats.Command.RemovePlayerPermissions");
        $this->setUsage("<player> <permission>");
        $this->setDescription("Remove a Permission from a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /removeplayerpermission" . " " . $this->getUsage());
            return false;
        }
		$user = $this->GPCore->getStats()->getGPUser($args[0]);
		
		if(!$user->hasAccount()) {
			$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Player");
			return false;
		}
        if(!$user->hasDatabasedPermission($args[1])) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $user->getUsername() . " doesn't have the Permission " . $args[1]);
            return false;
        } else {
            $user->removePermission($args[1]);

            $player = $user->getGPPlayer();
		
			if($player instanceof GPPlayer) {
				$player->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . " Removed the Permission " . $args[1] . " from you");
			}
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Removed the Permission " . $args[1] . " from " . $user->getUsername());
            return true;
        }
    }
}