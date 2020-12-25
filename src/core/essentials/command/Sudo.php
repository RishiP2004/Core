<?php

declare(strict_types = 1);

namespace core\essentials\command;

use core\Core;
use core\CorePlayer;

use core\essentials\Essentials;

use pocketmine\Server;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\event\player\PlayerChatEvent;

class Sudo extends PluginCommand {
    private $manager;

    public function __construct(Essentials $manager) {
        parent::__construct("sudo", Core::getInstance());

        $this->manager = $manager;

        $this->setPermission("core.essentials.command.sudo");
        $this->setUsage("<player> <command line : chat; chat message>");
        $this->setDescription("Run something as another Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /sudo " .  $this->getUsage());
            return false;
        }
        $player = Server::getInstance()->getPlayer($args[0]);

        if(!$player instanceof CorePlayer) {
            $sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not Online");
            return false;
        } else {
			$arg = implode(" ", $args);
			
			if(substr($arg, 0, 2) === "chat;") {
				Server::getInstance()->getPluginManager()->callEvent($event = new PlayerChatEvent($player, substr($arg, 2)));
				
				if(!$event->isCancelled()) {
					Server::getInstance()->broadcastMessage(Server::getInstance()->getLanguage()->translateString($event->getFormat(), [$event->getPlayer()->getDisplayName(), $event->getMessage()]), $event->getRecipients());
					$sender->sendMessage(Core::PREFIX . "Sent Message: " . $args[1] . " as the Player " . $player->getName());
				}
			} else {
				Server::getInstance()->dispatchCommand($player, $arg);
				$sender->sendMessage(Core::PREFIX. "Sent Command: " . $args[1] . " as the Player " . $player->getName());
			}
			return true;
        }
    }
}