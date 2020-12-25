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

class Transfer extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
		parent::__construct("transfer", Core::getInstance());

		$this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.transfer");
        $this->setUsage("<ip> <port> [player]");
        $this->setDescription("Transfer yourself or a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /transfer " . $this->getUsage());
            return false;
        }
        if(isset($args[2])) {
			if(!$sender->hasPermission($this->getPermission() . ".other")) {
				$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
				return false;
			}
            $player = Server::getInstance()->getPlayer($args[2]);

            if(!$player instanceof CorePlayer) {
                $sender->sendMessage(Core::ERROR_PREFIX . $args[2] . " is not Online");
                return false;
            } else {
                $player->transfer($args[0], (int) ($args[1] ?? 19132), $sender->getName() . " Transferred you to IP: " . $args[0] . " and Port: " . (int) ($args[1] ?? 19132));
				$sender->sendMessage(Core::PREFIX . "Transferring " . $player->getName() . " to IP: " . $args[0] . " and Port: " . (int) ($args[1] ?? 19132));
                return true;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
            return false;
        } else {
            $sender->transfer($args[0], (int) ($args[1] ?? 19132), "Transferring to IP: " . $args[0] . " and Port: " . (int) ($args[1] ?? 19132));
            return true;
        }
    }
}