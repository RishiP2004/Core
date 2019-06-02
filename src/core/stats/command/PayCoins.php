<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class PayCoins extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("paycoins", $core);

        $this->core = $core;

        $this->setPermission("core.stats.command.paycoins");
        $this->setUsage("<player> <amount>");
        $this->setDescription("Pay a Player Coins");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        }
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /paycoins" . " " . $this->getUsage());
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
        if($user->getCoins() + $args[1] > $this->core->getStats()->getMaximumEconomy("coins")) {
            $sender->sendMessage($this->core->getErrorPrefix() . $user->getName() . " will have over the Maximum amount of Coins");
            return false;
        }
        if($sender->getCoreUser()->getCoins() < $args[1]) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have enough Coins");
            return false;
        } else {
            $user->setCoins($user->getCoins() + $args[1]);
            $sender->getCoreUser()->setCoins($sender->getCoreUser()->getCoins() - $args[1]);

            $player = $this->core->getServer()->getPlayer($user->getName());
		
			if($player instanceof CorePlayer) {
				$player->sendMessage($this->core->getPrefix() . $sender->getName() . " paid you " . $this->core->getStats()->getEconomyUnit("coins") . $args[1]);
			}
            $sender->sendMessage($this->core->getPrefix() . "Paid " . $user->getName() . " " . $this->core->getStats()->getEconomyUnit("coins") . $args[1]);
            return true;
        }
    }
}