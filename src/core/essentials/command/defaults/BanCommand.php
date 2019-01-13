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

class BanCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("ban", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.Ban");
        $this->setUsage("<player> [reason]");
        $this->setDescription("Ban a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /ban" . " " . $this->getUsage());
            return false;
        }
		$user = $this->GPCore->getStats()->getGPUser($args[0]);
		
		if(!$user->hasAccount()) {
			$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Player");
			return false;
		}
        $banList = $sender->getServer()->getNameBans();

        if($banList->isBanned($user->getUsername())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $user->getUsername() . " is already Banned");
            return false;
        } else {
            $reason = implode(" ", $args[1]) !== "" ? $args[1] : "Not provided";

            $banList->addBan($user->getUsername(), $reason, null, $sender->getName());

			$player = $user->getGPPlayer();
		
			if($player instanceof GPPlayer) {
				$player->kick($this->GPCore->getBroadcast()->getPrefix() . "You are Banned!\n" . TextFormat::GRAY . "Banned by: " . $sender->getName() . "\n" . TextFormat::GRAY . "Reason: " . $reason);
			}
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "You have Banned " . $user->getUsername() . " for the Reason: " . $reason);
            $this->GPCore->getServer()->broadcastMessage($this->GPCore->getBroadcast()->getPrefix() . $user->getUsername() . " has been Banned by " . $sender->getName() . " for the Reason: "  . $reason);
            return true;
        }
    }
}