<?php

declare(strict_types = 1);

namespace core\essentials\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class ClearInventory extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("clearinventory", $core);

        $this->core = $core;

		$this->setAliases(["ci"]);
        $this->setPermission("core.essentials.clearinventory.command");
        $this->setUsage("[player]");
        $this->setDescription("Clear your or a Player's Inventory");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(isset($args[0])) {
            if(!$sender->hasPermission($this->getPermission() . ".other")) {
                $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
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
            }
			if($player->getGamemode() === CorePlayer::SPECTATOR) {
				$sender->sendMessage($this->core->getErrorPrefix() . $player->getName() . " is in " . $this->core->getServer()->getGamemodeString($args[0]->getGamemode()) . " Mode");
				return false;
            } else {
				$player->getInventory()->clearAll();
				$sender->sendMessage($this->core->getPrefix() . "Cleared " . $player->getName() . "'s Inventory");
				$player->sendMessage($this->core->getPrefix() . $sender->getName() . " Cleared your Inventory");
                return true; 
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
			$sender->getInventory()->clearAll();
			$sender->sendMessage($this->core->getPrefix() . "Cleared your Inventory");
            return true;
        }
    }
}
