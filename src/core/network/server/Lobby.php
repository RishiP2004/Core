<?php

declare(strict_types = 1);

namespace core\network\server;

use core\Core;
use core\CorePlayer;

use core\stats\Statistics;

use scoreboard\{
	Scoreboard,
	ScoreboardAction,
	ScoreboardDisplaySlot,
	ScoreboardManager,
	ScoreboardSort
};

use pocketmine\utils\TextFormat;

class Lobby extends Server {
    public function __construct() {
        parent::__construct("Lobby");
    }

    public function getIp() : string {
        return "lobby.gratonepix.me";
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
				$scoreboard->setLine(2, TextFormat::AQUA . "Players");
				$scoreboard->setLine(3, TextFormat::GRAY . Core::getInstance()->getNetwork()->getTotalOnlinePlayers() . "/" . Core::getInstance()->getNetwork()->getTotalMaxSlots());
				$scoreboard->setLine(4, TextFormat::GRAY . "------------");
				$scoreboard->setLine(5, TextFormat::AQUA . "Your Coins:");
				$scoreboard->setLine(6, TextFormat::GREEN . Statistics::UNITS["coins"] . $player->getCoreUser()->getCoins());
				$scoreboard->setLine(7, TextFormat::GRAY . "------------");
				$scoreboard->setLine(8, TextFormat::AQUA . "Your Balance:");
				$scoreboard->setLine(9, TextFormat::GREEN . Statistics::UNITS["balance"] . $player->getCoreUser()->getBalance());
				$scoreboard->setLine(7, TextFormat::GRAY . "------------");
				$scoreboard->setLine(9, TextFormat::DARK_GREEN . $this->getIp());
			break;
			case CorePlayer::POPUP:
				$player->sendPopup(Core::getInstance()->getPrefix() . "Welcome to the " . $this->getName());
			break;
		}
	}
}