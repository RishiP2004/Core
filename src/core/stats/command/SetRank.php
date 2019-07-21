<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use core\stats\rank\Rank;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class SetRank extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("setrank", $core);

        $this->core = $core;

        $this->setPermission("core.stats.command.setrank");
        $this->setUsage("<player> <rank>");
        $this->setDescription("Set a Player's Rank");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /setrank " . $this->getUsage());
            return false;
        }
		$this->core->getStats()->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
				return false;
			}
			$rank = $this->core->getStats()->getRank($args[1]);

			if(!$rank instanceof Rank) {
				$sender->sendMessage($this->core->getErrorPrefix() . $args[1] . " is not a valid Rank");
				return false;
			} else {
				$user->setRank($rank);

				$player = $this->core->getServer()->getPlayer($user->getName());
		
				if($player instanceof CorePlayer) {
					$player->sendMessage($this->core->getPrefix() . $sender->getName() . " set your Rank to " . $rank->getName());
				}
				$sender->sendMessage($this->core->getPrefix() . "Set " . $user->getName() . "'s Rank to " . $rank->getName());
				return true;
			}
        });
		return false;
    }
}