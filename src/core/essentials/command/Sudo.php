<?php

namespace core\essentials\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\event\player\PlayerChatEvent;

class Sudo extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("sudo", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.sudo.command");
        $this->setUsage("<player> <command line : chat; chat message>");
        $this->setDescription("Run something as another Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->etErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /sudo" . " " . $this->getUsage());
            return false;
        }
        $player = $this->core->getServer()->getPlayer($args[0]);

        if(!$player instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not Online");
            return false;
        }
        if(!$this->core->getStats()->getCoreUser($args[0])) {
            $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
            return false;
        } else {
			$arg = implode(" ", $args);
			
			if(substr($arg, 0, 2) === "chat;") {
				$this->core->getServer()->getPluginManager()->callEvent($event = new PlayerChatEvent($player, substr($arg, 2)));
				
				if(!$event->isCancelled()) {
					$this->core->getServer()->broadcastMessage($this->core->getServer()->getLanguage()->translateString($event->getFormat(), [$event->getPlayer()->getDisplayName(), $event->getMessage()]), $event->getRecipients());
					$sender->sendMessage($this->core->getPrefix() . "Sent Message: " . $args[1] . " as the Player " . $player->getName());
				}
			} else {
				$this->core->getServer()->dispatchCommand($player, $arg);
				$sender->sendMessage($this->core->getPrefix() . "Sent Command: " . $args[1] . " as the Player " . $player->getName());
			}
			return true;
        }
    }
}