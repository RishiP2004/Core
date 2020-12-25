<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use core\essentials\Essentials;

use core\stats\Stats;

use pocketmine\Server;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Deop extends PluginCommand {
    private $manager;

    public function __construct(Essentials $manager) {
        parent::__construct("deop", Core::getInstance());

        $this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.deop");
        $this->setUsage("<player>");
        $this->setDescription("Deop a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /deop " . $this->getUsage());
            return false;
        }
		Stats::getInstance()->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
				return false;
			}
			$player = $sender->getServer()->getOfflinePlayer($user->getName());

			if(!$player->isOp()) {
				$sender->sendMessage(Core::ERROR_PREFIX . $user->getName() . " is not Op");
				return false;
			} else {
				$player->setOp(false);

				if($player instanceof CorePlayer) {
					$player->sendMessage(Core::PREFIX . $sender->getName() . " Deoped you");
				}
				$sender->sendMessage(Core::PREFIX . $user->getName() . " is now Deoped");
				Server::getInstance()->broadcastMessage(Core::PREFIX . $user->getName() . " has been Deoped by " . $sender->getName());
				return true;
			}
        });
		return false;
    }
}