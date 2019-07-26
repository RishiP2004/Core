<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use core\stats\Statistics;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class SetCoins extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("setcoins", $core);

        $this->core = $core;

        $this->setPermission("core.stats.command.setcoins");
        $this->setUsage("<player> <amount>");
        $this->setDescription("Set a Player's Coins");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /setcoins " . $this->getUsage());
            return false;
        }
		if(!is_numeric($args[1])) {
            $sender->sendMessage($this->core->getErrorPrefix() . $args[1] . " is not a valid Number");
            return false;
        }
        if(is_float($args[1])) {
            $sender->sendMessage($this->core->getErrorPrefix() . $args[1] . " must be an Integer");
            return false;
        }
		$this->core->getStats()->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
				return false;
			}
			if($args[1] > Statistics::MAXIMUMS["coins"]) {
				$sender->sendMessage($this->core->getErrorPrefix() . $user->getName() . " will have over the Maximum amount of Coins");
				return false;
			} else {
				$user->setCoins((int) $args[1]);

				$player = $this->core->getServer()->getPlayer($user->getName());
		
				if($player instanceof CorePlayer) {
					$player->sendMessage($this->core->getPrefix() . $sender->getName() . " set your Coins to " . Statistics::UNITS["coins"] . $args[1]);
				}
				$sender->sendMessage($this->core->getPrefix() . "Set " . $user->getName() . "'s Coins to " . Statistics::UNITS["coins"] . $args[1]);
				return true;
			}
		});
		return false;
    }
}