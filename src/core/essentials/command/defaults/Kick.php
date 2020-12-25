<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use core\essentials\Essentials;

use pocketmine\Server;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class Kick extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
        parent::__construct("kick", Core::getInstance());

        $this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.kick");
        $this->setUsage("<all : player> [reason]");
        $this->setDescription("Kick a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /kick " . $this->getUsage());
            return false;
        } else {
			$reason = "Not provided";
			
			if(isset($args[1])) {
				$reason = implode(" ", $args[1]);
			}      
            if(strtolower($args[0]) === "all") {
                foreach(Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
                    $onlinePlayer->kick(TextFormat::GRAY . "Kicked by " . $sender->getName() . " for: " . $reason);
                    $sender->sendMessage(Core::PREFIX . "You have Kicked all Online Players for the Reason: " . $reason);
                }
            } else {
				$player = Server::getInstance()->getPlayer($args[0]);

				if(!$player instanceof CorePlayer) {
					$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not Online");
					return false;
				} else {
					$player->kick(TextFormat::GRAY . "Kicked by " . $sender->getName() . " for: " . $reason);
					$sender->sendMessage(Core::PREFIX . "You have Kicked " . $player->getName(). " for the Reason: " . $reason);
					Server::getInstance()->broadcastMessage(Core::PREFIX . $player->getName() . " has been Kicked by " . $sender->getName() . " for the Reason: " . $reason);
				}
			}
            return true;
        }
    }
}