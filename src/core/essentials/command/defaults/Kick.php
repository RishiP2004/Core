<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class Kick extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("kick", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.kick.command");
        $this->setUsage("<all : player> [reason]");
        $this->setDescription("Kick a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /kick " . $this->getUsage());
            return false;
        } else {
			$reason = "Not provided";
			
			if(isset($args[1])) {
				$reason = implode(" ", $args[1]);
			}      
            if(strtolower($args[0]) === "all") {
                foreach($this->core->getServer()->getOnlinePlayers() as $onlinePlayer) {
                    $onlinePlayer->kick($this->core->getPrefix() . "You have been Kicked!\n" . TextFormat::GRAY . "Kicked by: " . $sender->getName() . "\n" . TextFormat::GRAY . "Reason: " . $reason);
                    $sender->sendMessage($this->core->getPrefix() . "You have Kicked all Online Players for the Reason: " . $reason);
                }
            } else {
				$player = $this->core->getServer()->getPlayer($args[0]);

				if(!$player instanceof CorePlayer) {
					$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not Online");
					return false;
				} else {
					$player->kick($this->core->getPrefix() . "You have been Kicked!\n" . TextFormat::GRAY . "Kicked by: " . $sender->getName() . "\n" . TextFormat::GRAY . "Reason: " . $reason);
					$sender->sendMessage($this->core->getPrefix() . "You have Kicked " . $player->getName(). " for the Reason: " . $reason);
					$this->core->getServer()->broadcastMessage($this->core->getPrefix() . $player->getName() . " has been Kicked by " . $sender->getName() . " for the Reason: " . $reason);
				}
			}
            return true;
        }
    }
}