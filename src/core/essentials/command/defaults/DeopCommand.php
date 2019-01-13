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

class DeopCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("deop", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.Deop");
        $this->setUsage("<player>");
        $this->setDescription("Deop a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /deop" . " " . $this->getUsage());
            return false;
        }
        $user = $this->GPCore->getStats()->getGPUser($args[0]);

        if(!$user->hasAccount()) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Player");
            return false;
        }
        $player = $sender->getServer()->getOfflinePlayer($args[0]);

        if(!$player->isOp()) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not Op");
            return false;
        } else {
            $player->setOp(false);

            if($player instanceof GPPlayer) {
                $player->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . " Deoped you");
            }
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $args[0] . " is now Deoped");
            $this->GPCore->getServer()->broadcastMessage($this->GPCore->getBroadcast()->getPrefix() . $args[0] . " has been Deoped by " . $sender->getName());
            return true;
        }
    }
}