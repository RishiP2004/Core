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

use pocketmine\utils\TextFormat;

class KickCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("kick", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.Kick");
        $this->setUsage("<all : player> [reason]");
        $this->setDescription("Kick a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /kick" . " " . $this->getUsage());
            return false;
        }
        $user = $this->GPCore->getStats()->getGPUser($args[0]);

        if(!$user->hasAccount() && $args[0] !== "all") {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[1] . " is not a valid Player");
            return false;
        } else {
            $reason = implode(" ", $args[1]) !== "" ? $args[1] : "Not provided";

            if($args[0] === "all") {
                foreach($this->GPCore->getServer()->getOnlinePlayers() as $onlinePlayer) {
                    $onlinePlayer->kick($this->GPCore->getBroadcast()->getPrefix() . "You have been Kicked!\n" . TextFormat::GRAY . "Kicked by: " . $sender->getName() . "\n" . TextFormat::GRAY . "Reason: " . $reason);
                    $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "You have Kicked all Online Players for the Reason: " . $reason);
                }
            }
            $player = $user->getGPPlayer();

            if(!$player instanceof GPPlayer) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $user->getUsername() . " is not Online");
                return false;
            } else {
                $player->kick($this->GPCore->getBroadcast()->getPrefix() . "You have been Kicked!\n" . TextFormat::GRAY . "Kicked by: " . $sender->getName() . "\n" . TextFormat::GRAY . "Reason: " . $reason);
                $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "You have Kicked " . $user->getUsername(). " for the Reason: " . $reason);
                $this->GPCore->getServer()->broadcastMessage($this->GPCore->getBroadcast()->getPrefix() . $user->getUsername() . " has been Kicked by " . $sender->getName() . " for the Reason: " . $reason);
            }
            return true;
        }
    }
}