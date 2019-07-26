<?php

declare(strict_types = 1);

namespace core\network\server;

use core\Core;
use core\CorePlayer;

use factions\FactionsPlayer;

use core\stats\Statistics;

use scoreboard\{
	Scoreboard,
	ScoreboardAction,
	ScoreboardDisplaySlot,
	ScoreboardManager,
	ScoreboardSort
};

use pocketmine\utils\TextFormat;

class Factions extends Server {
    public function __construct() {
        parent::__construct("Factions");
		
		$this->setWhitelisted();
    }

    public function getIp() : string {
        return "facs.gratonepix.me";
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
				if(ScoreboardManager::getId(Core::getInstance()->getPrefix() . $this->getName()) === null) {
					$scoreboard = new Scoreboard(Core::getInstance()->getPrefix() . $this->getName(), ScoreboardAction::CREATE);
				} else {
					$scoreboard = new Scoreboard(Core::getInstance()->getPrefix() . $this->getName(), ScoreboardAction::MODIFY);
				}
				$scoreboard->create(ScoreboardDisplaySlot::SIDEBAR, ScoreboardSort::ASCENDING);
				$scoreboard->setLine(1, TextFormat::GRAY . "------------");
				$scoreboard->setLine(2, TextFormat::RED . "Players In Server:");
				$scoreboard->setLine(3, TextFormat::GRAY . $this->getOnlinePlayers() . "/" . $this->getMaxSlots());
				$scoreboard->setLine(4, TextFormat::GRAY . "------------");
				$scoreboard->setLine(5, TextFormat::RED . "Your Coins:");
				$scoreboard->setLine(6, TextFormat::GREEN . Statistics::UNITS["coins"] . $player->getCoreUser()->getCoins());
				$scoreboard->setLine(7, TextFormat::GRAY . "------------");
				$scoreboard->setLine(8, TextFormat::RED . "Your Balance:");
				$scoreboard->setLine(9, TextFormat::GREEN . Statistics::UNITS["balance"] . $player->getCoreUser()->getBalance());
				$scoreboard->setLine(7, TextFormat::GRAY . "------------");
				//TODO: Faction, etc
				$scoreboard->setLine(9, TextFormat::DARK_GREEN . $this->getIp());
			break;
			case CorePlayer::POPUP:
				$msg = Core::getInstance()->getPrefix() . "Welcome to the " . $this->getName() . "\n";

				if($player instanceof FactionsPlayer) {
					if($player->isCombatTagged()) {
						$msg .= Core::getInstance()->getErrorPrefix() . "You are Combat Tagged for " . $player->getCombatTagTime() . " seconds\n";
					}
					if($player->isInCooldown($player::COOLDOWN_GAPPLE)) {
						$msg .= Core::getInstance()->getErrorPrefix() . "You are in Gapple Cooldown for " . $player->getCooldownTime($player::COOLDOWN_GAPPLE) . " seconds\n";
					}
					if($player->isInCooldown($player::COOLDOWN_ENDER_PEARL)) {
						$msg .= Core::getInstance()->getErrorPrefix() . "You are in Ender Pearl Cooldown for " . $player->getCooldownTime($player::COOLDOWN_GAPPLE) . " seconds\n";
					}
				}
				$player->sendPopup($msg);
			break;
		}
	}
}