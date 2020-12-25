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

class SetSpawn extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
		parent::__construct("setespawn", Core::getInstance());

		$this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.setspawn");
        $this->setUsage("[player]");
        $this->setDescription("Set the Spawn");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(count($args) < 1) {
            if(!$sender instanceof CorePlayer) {
                $sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
                return false;
            }
        }
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        } else {
            $sender->getLevel()->setSpawnLocation($sender);
            $sender->getServer()->setDefaultLevel($sender->getLevel());
            $sender->sendMessage(Core::PREFIX . "Server's Spawn changed to Level: " . $sender->getLevel()->getName() . " at X: " . (int) $sender->getX() . ", Y: " . (int) $sender->getY() . ", Z: " . (int) $sender->getZ());
            Server::getInstance()->broadcastMessage(Core::PREFIX . "Server's Spawn changed to Level: " . $sender->getLevel()->getName() . " at X: " . (int) $sender->getX() . ", Y: " . (int) $sender->getY() . ", Z: " . (int) $sender->getZ() . " by " . $sender->getName());
            return true;
        }
    }
}