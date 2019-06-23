<?php

declare(strict_types = 1);

namespace core\anticheat\cheat;

use core\Core;

use core\CorePlayer;

class AutoClicker extends Cheat {
	public function set(CorePlayer $player) {
		$this->player = $player;
	}

	public function getId() : string {
		return self::AUTO_CLICKER;
	}

	public function getName() : string {
		return "Auto Clicker";
	}

	public function maxCheating() : int {
		return 8;
	}

	public function getPunishment() {
		return [self::KICK, "Auto Clicker; Warning (" . $this->getPlayer()->getCoreUser()->getCheatHistory()[$this->getId()] . ")"];
	}

	public function getMainPunishment() {
		return [self::BAN, "Auto Clicker; Too many Chances given (" . $this->maxCheating() . ")", "10 days"];
	}

	public function onRun() {
		$interacts = $this->getPlayer()->addToInteract();

		if($interacts["amount"] >= Core::getInstance()->getAntiCheat()->getAutoClickAmount()) {
			Core::getInstance()->getServer()->getLogger()->warning(Core::getInstance()->getErrorPrefix() . $this->getPlayer()->getName() . " seems to have an Auto Clicker");
			$this->final();
		}
	}
}