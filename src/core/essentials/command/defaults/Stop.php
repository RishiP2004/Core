<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;

use core\essentials\Essentials;

use pocketmine\Server;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Stop extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
		parent::__construct("stop", Core::getInstance());

        $this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.stop");
        $this->setDescription("Stop the Server");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        } else {
            Server::getInstance()->shutdown();
            $sender->sendMessage(Core::PREFIX . "Stopped the Server");
            Server::getInstance()->broadcastMessage(Core::PREFIX . $sender->getName() . " Stopped the Server");
            return true;
        }
    }
}