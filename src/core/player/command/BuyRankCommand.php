<?php

declare(strict_types = 1);

namespace core\player\command;

use core\Core;

use core\player\PlayerManager;
use core\player\rank\Rank;
use core\player\rank\RankIds;
use core\player\Statistics;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;

use pocketmine\command\CommandSender;

class BuyRankCommand extends BaseCommand {
    public function prepare() : void {
    	$this->setPermission("buyrank.command");
    	$this->addConstraint(new InGameRequiredConstraint($this));
	}

    public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
        if($sender->getCoreUser()->getRank()->getValue() !== RankIds::PLAYER) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You already have a RankCommand that is OG or higher");
            return;
        }
        if($sender->getCoreUser()->getCoins() < PlayerManager::getInstance()->getRankByName("OG")->getFreePrice()) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have enough Coins");
            return;
        }
		$sender->getCoreUser()->setRank(PlayerManager::getInstance()->getRankByName("OG"));
		$sender->getCoreUser()->setCoins($sender->getCoreUser()->getCoins() - PlayerManager::getInstance()->getRankByName("OG")->getFreePrice());
		$sender->sendMessage(Core::PREFIX . "You have bought the OG Rank for " . Statistics::COIN_UNIT . PlayerManager::getInstance()->getRankByName("OG")->getFreePrice());
    }
}