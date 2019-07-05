<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\Server;

class Gamemode extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("gamemode", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.gamemode.command");
        $this->setUsage("<gamemode> [player]");
        $this->setDescription("Set yours or a Player's Gamemode");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /gamemode" . " " . $this->getUsage());
            return false;
        }
		$gamemode = Server::getGamemodeFromString($args[0]);

		if($gamemode === -1) {
			$sender->sendMessage($this->core->getErrorPrefix() . $gamemode . " is not a valid Gamemode");
            return false;
		}
        if(isset($args[1])) {
			if(!$sender->hasPermission($this->getPermission() . ".other")) {
				$sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
				return false;
			}
            $player = $this->core->getServer()->getPlayer($args[0]);

            if(!$player instanceof CorePlayer) {
                $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not Online");
                return false;
            }
			if($player->getGamemode() === $gamemode) {
				$sender->sendMessage($this->core->getErrorPrefix() . $gamemode . " is already " . $player->getName() . "'s Gamemode");
				return false;
            } else {
                $player->setGamemode($gamemode);
				$sender->sendMessage($this->core->getPrefix() . "Set " . $player->getName() . "'s Gamemode to " . $gamemode);
				$player->sendMessage($sender->getName() . " set your Gamemode to " . $gamemode);
                return true;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
            $sender->setGamemode($gamemode);
			$sender->sendMessage($this->core->getPrefix() . "Set your Gamemode to " . $gamemode);
            return true;
        }
    }
}