<?php

declare(strict_types = 1);

namespace core\anticheat\cheat;

use core\Core;

use core\player\CorePlayer;

class AutoClicker extends Cheat {
	public function set(CorePlayer $player) : void {
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

	public function getPunishment() : array {
		return [
			self::KICK,
			"Auto Clicker; Warning (" . $this->getPlayer()->getCoreUser()->getCheatHistory()[$this->getId()] . ")"
		];
	}

	public function getMainPunishment() : array {
		return [
			self::BAN,
			"Auto Clicker; Too many Chances given (" . $this->maxCheating() . ")",
			"10 days"
		];
	}

	public function onRun() : void {
		$interacts = $this->getPlayer()->getInteracts();

		if($interacts["amount"] >= self::AUTO_CLICK_AMOUNT) {
			Core::getInstance()->getServer()->getLogger()->warning(Core::ERROR_PREFIX . $this->getPlayer()->getName() . " seems to have an Auto Clicker");
			$this->final();
		}
	}
}