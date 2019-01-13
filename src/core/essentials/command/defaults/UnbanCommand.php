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

class UnbanCommand extends PluginCommand {
	private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("unban", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.Unban");
        $this->setUsage("<player>");
        $this->setDescription("Unban a Player");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /unban" . " " . $this->getUsage());
            return false;
        }
        $user = $this->GPCore->getStats()->getGPUser($args[0]);

        if(!$user->hasAccount()) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Player");
            return false;
        }
        $banList = $this->GPCore->getServer()->getNameBans();

        if(!$banList->isBanned($user->getUsername())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $user->getUsername() . " is not Banned");
            return false;
        } else {
            $banList->remove($user->getUsername());

            $player = $user->getGPPlayer();

            if($player instanceof GPPlayer) {
                $player->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "You have been Unbanned By: " . $sender->getName());
            }
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "You have Unbanned " . $user->getUsername());
            $this->GPCore->getServer()->broadcastMessage($this->GPCore->getBroadcast()->getPrefix() . $user->getUsername() . " has been Unbanned by " . $sender->getName());
            return true;
        }
    }
}
