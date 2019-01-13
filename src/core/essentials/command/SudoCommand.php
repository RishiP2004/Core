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

use pocketmine\event\player\PlayerChatEvent;

class SudoCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("sudo", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Command.Sudo");
        $this->setUsage("<player> <command line : chat; chat message>");
        $this->setDescription("Run something as another Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /sudo" . " " . $this->getUsage());
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
			$arg = implode(" ", $args);
			
			if(substr($arg, 0, 2) === "chat;") {
				$this->GPCore->getServer()->getPluginManager()->callEvent($event = new PlayerChatEvent($player, substr($arg, 2)));
				
				if(!$event->isCancelled()){
					$this->GPCore->getServer()->broadcastMessage($this->GPCore->getServer()->getLanguage()->translateString($event->getFormat(), [$event->getPlayer()->getDisplayName(), $event->getMessage()]), $event->getRecipients());
					$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Sent Message: " . $args[1] . " as the Player " . $player->getName());
				}
			} else {
				$this->GPCore->getServer()->dispatchCommand($player, $arg);
				$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Sent Command: " . $args[1] . " as the Player " . $player->getName());
			}
			return true;
        }
    }
}