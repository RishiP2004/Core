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

class Gamemode extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
        parent::__construct("gamemode", Core::getInstance());

        $this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.gamemode");
        $this->setUsage("<gamemode> [player]");
        $this->setDescription("Set yours or a Player's Gamemode");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /gamemode " . $this->getUsage());
            return false;
        }
		$gamemode = Server::getGamemodeFromString($args[0]);

		if($gamemode === -1) {
			$sender->sendMessage(Core::ERROR_PREFIX . $gamemode . " is not a valid Gamemode");
            return false;
		}
        if(isset($args[1])) {
			if(!$sender->hasPermission($this->getPermission() . ".other")) {
				$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
				return false;
			}
            $player = Server::getInstance()->getPlayer($args[1]);

            if(!$player instanceof CorePlayer) {
                $sender->sendMessage(Core::ERROR_PREFIX . $args[1] . " is not Online");
                return false;
            }
			if($player->getGamemode() === $gamemode) {
				$sender->sendMessage(Core::ERROR_PREFIX . $gamemode . " is already " . $player->getName() . "'s Gamemode");
				return false;
            } else {
                $player->setGamemode($gamemode);
				$sender->sendMessage(Core::PREFIX . "Set " . $player->getName() . "'s Gamemode to " . $gamemode);
				$player->sendMessage($sender->getName() . " set your Gamemode to " . $gamemode);
                return true;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
            return false;
        } else {
            $sender->setGamemode($gamemode);
			$sender->sendMessage(Core::PREFIX . "Set your Gamemode to " . $gamemode);
            return true;
        }
    }
}