<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class BuyRank extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("buyrank", $core);

        $this->core = $core;

        $this->setPermission("core.stats.command.buyrank");
        $this->setDescription("Buy a Rank with Coins");
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
        if($sender->getCoreUser()->getRank()->getName() !== "Player") {
            $sender->sendMessage($this->core->getPrefix() . "You already have a Rank that is OG or higher");
            return false;
        }
        if($sender->getCoreUser()->getCoins() < $this->core->getStats()->getRank("OG")->getFreePrice()) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have enough Coins");
            return false;
        } else {
            $sender->getCoreUser()->setRank($this->core->getStats()->getRank("OG"));
            $sender->getCoreUser()->setCoins($sender->getCoreUser()->getCoins() - $this->core->getStats()->getRank("OG")->getFreePrice());
            $sender->sendMessage($this->core->getPrefix() . "You have bought the Rank OG for " . $this->core->getStats()->getEconomyUnit("coins") . $this->core->getStats()->getRank("OG")->getFreePrice());
            return true;
        }
    }
}