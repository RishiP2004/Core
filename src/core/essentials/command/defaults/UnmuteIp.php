<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class UnmuteIp extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("unblock-ip", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.unblock-ip.command");
        $this->setUsage("<player : ip>");
        $this->setDescription("Un-Ip Block a Player");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /unmute-ip" . " " . $this->getUsage());
            return false;
        }
        if(!$user = $this->core->getStats()->getCoreUser($args[0]) or !preg_match("/^([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])\\.([01]?\\d\\d?|2[0-4]\\d|25[0-5])$/", $args[0])) {
            $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
            return false;
        }
        $ip = $args[0];
        $player = null;

        if($user) {
            $ip = $user->getIp();
            $player = $this->core->getServer()->getPlayer($user->getName());
        }
        $muteList = $this->core->getEssentials()->getIpMutes();

        if(!$muteList->isBanned($ip)) {
            $sender->sendMessage($this->core->getErrorPrefix() . $ip . " is not Muted");
            return false;
        } else {
            $muteList->remove($ip);

            if($player instanceof CorePlayer) {
                $player->sendMessage($this->core->getPrefix() . "You have been Un-Ip Muted By: " . $sender->getName());
            }
            $sender->sendMessage($this->core->getPrefix() . "You have Un-Ip Muted " . $user->getName());
            $this->core->getServer()->broadcastMessage($this->core->getPrefix() . $user->getName() . " has been Un-Ip Muted by " . $sender->getName());
            return true;
        }
    }
}