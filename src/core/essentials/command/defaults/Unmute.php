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

class Unmute extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
		parent::__construct("unmute", Core::getInstance());

		$this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.unmute");
        $this->setUsage("<player>");
        $this->setDescription("Unmute a Player");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /unmute " . $this->getUsage());
            return false;
        }
		Stats::getInstance()->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
				return false;
			}
			$muteList = $this->manager->getNameMutes();

			if(!$muteList->isBanned($user->getName())) {
				$sender->sendMessage(Core::ERROR_PREFIX . $user->getName() . " is not Muted");
				return false;
			} else {
				$muteList->remove($user->getName());

				$player = Server::getInstance()->getPlayer($user->getName());

				if($player instanceof CorePlayer) {
					$player->sendMessage(Core::PREFIX . "You have been Unmuted By: " . $sender->getName());
				}
				$sender->sendMessage(Core::PREFIX . "You have Unmuted " . $user->getName());
				Server::getInstance()->broadcastMessage(Core::PREFIX . $user->getName() . " has been Unmuted by " . $sender->getName());
				return true;
			}
        });
		return false;
    }
}