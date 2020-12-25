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

class Unblock extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
		parent::__construct("unblock", Core::getInstance());

		$this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.unblock");
        $this->setUsage("<player>");
        $this->setDescription("Unblock a Player");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /unblock " . $this->getUsage());
            return false;
        }
		Stats::getInstance()->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
				return false;
			}
			$blockList = $this->manager->getNameBlocks();

			if(!$blockList->isBanned($user->getName())) {
				$sender->sendMessage(Core::ERROR_PREFIX . $user->getName() . " is not Blocked");
				return false;
			} else {
				$blockList->remove($user->getName());

				$player = Server::getInstance()->getPlayer($user->getName());

				if($player instanceof CorePlayer) {
					$player->sendMessage(Core::PREFIX . "You have been Unblocked By: " . $sender->getName());
				}
				$sender->sendMessage(Core::PREFIX . "You have Unblocked " . $user->getName());
				Server::getInstance()->broadcastMessage(Core::PREFIX . $user->getName() . " has been Unblocked by " . $sender->getName());
				return true;
			}
        });
		return false;
    }
}
