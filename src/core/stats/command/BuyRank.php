<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use core\stats\Stats;

use core\stats\rank\Rank;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class BuyRank extends PluginCommand {
    private $manager;

    public function __construct(Stats $manager) {
        parent::__construct("buyrank", Core::getInstance());

        $this->manager = $manager;

        $this->setPermission("core.stats.command.buyrank");
        $this->setDescription("Buy a Rank with Coins");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
            return false;
        }
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if($sender->getCoreUser()->getRank()->getValue() !== Rank::DEFAULT) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You already have a Rank that is OG or higher");
            return false;
        }
        if($sender->getCoreUser()->getCoins() < $this->manager->getRank("OG")->getFreePrice()) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have enough Coins");
            return false;
        } else {
            $sender->getCoreUser()->setRank($this->manager->getRank("OG"));
            $sender->getCoreUser()->setCoins($sender->getCoreUser()->getCoins() - $this->manager->getRank("OG")->getFreePrice());
            $sender->sendMessage(Core::PREFIX . "You have bought the Rank OG for " . $this->manager::UNITS["coins"] . $this->manager->getRank("OG")->getFreePrice());
            return true;
        }
    }
}