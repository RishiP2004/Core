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

class BlockIpCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("block-ip", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.BlockIP");
        $this->setUsage("<player : ip> [reason]");
        $this->setDescription("Block an IP or Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /block-ip" . " " . $this->getUsage());
            return false;
        }
        $user = $this->GPCore->getStats()->getGPUser($args[0]);

        if(!$user->hasAccount()) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Player");
            return false;
        }
        $banList = $this->GPCore->getEssentials()->getDefaults()->getIpBlocks();
        $reason = implode(" ", $args[1]) !== "" ? $args[1] : "Not provided";

        if(preg_match("/^([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])$/", $args[0])) {
            if($banList->isBanned($user->getUsername())) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $user->getUsername() . " is already IP Blocked");
                return false;
            }
            $banList->addBan($user->getUsername(), $reason, null, $sender->getName());
        } else {
            $player = $user->getGPPlayer();

            if(!$player instanceof GPPlayer) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid IP");
                return false;
            } else {
                if($banList->isBanned($user->getUsername())) {
                    $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $user->getUsername() . " is already IP Blocked");
                    return false;
                }
                $banList->addBan($user->getIp());
                return true;
            }
        }
        foreach($sender->getServer()->getOnlinePlayers() as $onlinePlayers) {
            if($onlinePlayers->getAddress() === $args[0]) {
                $onlinePlayers->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "You have been IP Blocked By: " . $sender->getName() . " for the Reason: " . $reason);
            }
        }
        $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "You have IP Blocked " . $user->getUserName() . " for the Reason: " . $reason);
        return true;
    }
}