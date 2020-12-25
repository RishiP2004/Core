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

class Reload extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
		parent::__construct("plugins", Core::getInstance());

		$this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.reload");
        $this->setDescription("Reload the Server");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        } else {
            $sender->sendMessage(Core::PREFIX . "Reloading the Server...");
            Server::getInstance()->broadcastMessage(Core::PREFIX . $sender->getName() . " is Reloading the Server...");
            Server::getInstance()->reload();
            $sender->sendMessage(Core::PREFIX . "Reloaded the Server");
            Server::getInstance()->broadcastMessage(Core::PREFIX . $sender->getName() . " Reloaded the Server");
            return true;
        }
    }
}