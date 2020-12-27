<?php

declare(strict_types = 1);

namespace core\network\server;

use core\Core;
use core\CorePlayer;

use factions\FactionsPlayer;

use core\network\Network;

use core\stats\Statistics;

use scoreboard\{
	Scoreboard,
	ScoreboardAction,
	ScoreboardDisplaySlot,
	ScoreboardManager,
	ScoreboardSort
};

use pocketmine\utils\TextFormat;

class Survival extends Server {
    public function __construct() {
        parent::__construct("Survival");
		
		$this->setWhitelisted();
    }

    public function getIp() : string {
        return "surv.gratonepix.me";
    }

    public function getPort() : int {
        return 19132;
    }

    public function getIcon() : string {
       return "";
    }

	public function addHud(int $type, CorePlayer $player) {
		switch($type) {
			case CorePlayer::SCOREBOARD:
				if(ScoreboardManager::getId(TextFormat::BOLD . Core::PREFIX) === null) {
					$scoreboard = new Scoreboard(Core::PREFIX . $this->getName(), ScoreboardAction::CREATE);
				} else {
					$scoreboard = new Scoreboard(Core::PREFIX . $this->getName(), ScoreboardAction::MODIFY);
				}
				$players = [];

				foreach(Core::getInstance()->getNetwork()->getServers() as $server) {
					$players[] = count($server->getOnlinePlayers());
				}
				$scoreboard->create(ScoreboardDisplaySlot::SIDEBAR, ScoreboardSort::DESCENDING);
				$scoreboard->addDisplay($player);
				$scoreboard->setLine(1, TextFormat::GRAY . "------------");
				$scoreboard->setLine(2, TextFormat::GREEN . "Player Counts:");
				$scoreboard->setLine(3, TextFormat::GOLD . "     Total: " . count(Network::getInstance()->getTotalOnlinePlayers()) . "/" . Network::getInstance()->getTotalMaxSlots());
				$scoreboard->setLine(4, TextFormat::GOLD . "     " . $this->getName() . ": " . count($this->getOnlinePlayers()) . "/" . $this->getMaxSlots());
				$scoreboard->setLine(5, TextFormat::GRAY . "------------");
				$scoreboard->setLine(6, TextFormat::GREEN . "Your Coins: " . Statistics::COIN_UNIT . $player->getCoreUser()->getCoins());
				//$scoreboard->setLine(7, TextFormat::GREEN . "Your Balance: " .  Statistics::BALANCE_UNIT . $player->getCoreUser()->getBalance());
				$scoreboard->setLine(8, TextFormat::GREEN . "Your Rank: " . $player->getCoreUser()->getRank()->getFormat());
				$scoreboard->setLine(9, TextFormat::GRAY . "------------");
				$scoreboard->setLine(10, TextFormat::DARK_RED . "gratonepix.buycraft.net");
				$scoreboard->addDisplay($player);
				//TODO: Survival features
			break;
			case CorePlayer::POPUP:
				$msg = Core::PREFIX . "Welcome to the " . $this->getName() . "\n";
				$player->sendPopup($msg);
			break;
		}
	}
}