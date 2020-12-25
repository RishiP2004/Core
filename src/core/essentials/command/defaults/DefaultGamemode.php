<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;

use core\essentials\Essentials;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\Server;

class DefaultGamemode extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
        parent::__construct("defaultgamemode", Core::getInstance());

        $this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.defaultgamemode");
        $this->setUsage("<gamemode>");
        $this->setDescription("Set the Default Gamemode");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /defaultgamemode " . $this->getUsage());
            return false;
        }
        if(Server::getGamemodeFromString($args[0]) === -1) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Unknown Gamemode");
            return false;
        }
        if(Server::getInstance()->getDefaultGamemode() === $args[0]) {
            $sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is already the Default Gamemode");
            return false;
        } else {
            Server::getInstance()->setConfigInt("gamemode", $args[0]);
            $sender->sendMessage(Core::PREFIX . "Set Default Gamemode to " . $args[0]);
            return true;
        }
    }
}