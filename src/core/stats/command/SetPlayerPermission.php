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

class SetPlayerPermissionCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("setplayerpermission", $GPCore);

        $this->GPCore = $GPCore;

        $this->setAliases(["setpperm"]);
        $this->setPermission("GPCore.Stats.Command.SetPlayerPermissions");
        $this->setUsage("<player> <permission(s)>");
        $this->setDescription("Set Permissions of a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /setplayerpermission" . " " . $this->getUsage());
            return false;
		}
        $user = $this->GPCore->getStats()->getGPUser($args[0]);
		
		if(!$user->hasAccount()) {
			$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Player");
			return false;
        } else {
            $user->setPermission(explode(", ", $args[1]));

            $player = $user->getGPPlayer();
		
			if($player instanceof GPPlayer) {
				$player->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . " set your Permission(s) to " . implode(", ", $args[1]));
			}
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Set the Permission(s) of " . $user->getUsername() . " to " . implode(", ", $args[1]));
            return true;
        }
    }
}