<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use core\stats\Stats;

use core\stats\rank\Rank;

use pocketmine\Server;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class SetRank extends PluginCommand {
    private $manager;

    public function __construct(Stats $manager) {
        parent::__construct("setrank", Core::getInstance());

        $this->manager = $manager;

        $this->setPermission("core.stats.command.setrank");
        $this->setUsage("<player> <rank>");
        $this->setDescription("Set a Player's Rank");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /setrank " . $this->getUsage());
            return false;
        }
		$this->manager->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
				return false;
			}
			$rank = $this->manager->getRank($args[1]);

			if(!$rank instanceof Rank) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args[1] . " is not a valid Rank");
				return false;
			}
			if($user->getRank() === $rank) {
				$sender->sendMessage(Core::ERROR_PREFIX . $user->getName() . " already has the Rank " . $rank->getName());
				return false;
			} else {
				$user->setRank($rank);

				$player = Server::getInstance()->getPlayer($user->getName());
		
				if($player instanceof CorePlayer) {
					$player->sendMessage(Core::PREFIX . $sender->getName() . " set your Rank to " . $rank->getName());
				}
				$sender->sendMessage(Core::PREFIX . "Set " . $user->getName() . "'s Rank to " . $rank->getName());
				return true;
			}
        });
		return false;
    }
}