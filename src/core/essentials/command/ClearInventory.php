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

class ClearInventory extends PluginCommand {
    private $manager;

    public function __construct(Essentials $manager) {
        parent::__construct("clearinventory", Core::getInstance());

        $this->manager = $manager;

		$this->setAliases(["ci"]);
        $this->setPermission("core.essentials.command.clearinventory");
        $this->setUsage("[player]");
        $this->setDescription("Clear your or a Player's Inventory");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(isset($args[0])) {
            if(!$sender->hasPermission($this->getPermission() . ".other")) {
                $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
                return false;
            }
            $player = Server::getInstance()->getPlayer($args[0]);

            if(!$player instanceof CorePlayer) {
                $sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not Online");
                return false;
			}
			if($player->getGamemode() === CorePlayer::SPECTATOR) {
				$sender->sendMessage(Core::ERROR_PREFIX . $player->getName() . " is in " . Server::getInstance()->getGamemodeString($args[0]->getGamemode()) . " Mode");
				return false;
            } else {
				$player->getInventory()->clearAll();
				$sender->sendMessage(Core::PREFIX . "Cleared " . $player->getName() . "'s Inventory");
				$player->sendMessage(Core::PREFIX . $sender->getName() . " Cleared your Inventory");
                return true; 
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
            return false;
        } else {
			$sender->getInventory()->clearAll();
			$sender->sendMessage(Core::PREFIX . "Cleared your Inventory");
            return true;
        }
    }
}
