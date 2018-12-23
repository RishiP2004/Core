<?php
/**
 *    ___________________________
 *   /  _____/\______   \_   ___ \  ___________   ____
 *  /   \  ___ |     ___/    \  \/ /  _ \_  __ \_/ __ \
 *  \    \_\  \|    |   \     \___(  <_> )  | \/\  ___/
 *   \______  /|____|    \______  /\____/|__|    \___  >
 *          \/                  \/                   \/
 */
namespace GPCore\Stats\Commands;

use GPCore\GPCore;

use GPCore\Stats\Objects\GPPlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class BuyRankCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("buyrank", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Stats.Command.BuyRank");
        $this->setDescription("Buy a Rank with Coins");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender instanceof GPPlayer) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        }
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if($sender->getGPUser()->getRank()->getName() !== "Player") {
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "You already have a Rank that is Gametoner or higher");
            return false;
        }
        if($sender->getGPUser()->getCoins() < $this->GPCore->getStats()->getCosts("Gametoner")) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have enough Coins");
            return false;
        } else {
            $sender->getGPUser()->setRank($this->GPCore->getStats()->getRankFromString("Gametoner"));
            $sender->getGPUser()->setCoins($sender->getGPUser()->getCoins() - $this->GPCore->getStats()->getCosts("Gametoner"));
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "You have bought the Rank Gametoner for " . $this->GPCore->getStats()->getEconomyUnit("Coins") . $this->GPCore->getStats()->getCosts("Gametoner"));
            return true;
        }
    }
}