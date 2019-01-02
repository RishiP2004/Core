<?php

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class TakeCoins extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("takecoins", $core);

        $this->core = $core;

        $this->setPermission("core.stats.command.takecoins");
        $this->setUsage("<player> <amount>");
        $this->setDescription("Take Coins from a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /takecoins" . " " . $this->getUsage());
            return false;
        }
		$user = $this->core->getStats()->getCoreUser($args[0]);
		
		if(!$user) {
			$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
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
        if($user->getCoins() - $args[1] < 0) {
            $sender->sendMessage($this->core->getErrorPrefix() . $user->getName() . " will have less than 0 Coins");
            return false;
        } else {
            $user->setCoins($user->getCoins() - $args[1]);

            $player = $this->core->getServer()->getPlayer($user->getName());
		
			if($player instanceof CorePlayer) {
				$player->sendMessage($this->core->getPrefix() . $sender->getName() . " Took " . $this->core->getStats()->getEconomyUnit("Coins") . $args[1] . " from you");
			}
            $sender->sendMessage($this->core->getPrefix() . "Took away " . $this->core->getStats()->getEconomyUnit("Coins") . $args[1] . " from " . $user->getName());
            return true;
        }
    }
}