<?php

declare(strict_types = 1);

namespace core\anticheat\cheat;

use core\Core;
use core\CorePlayer;

use pocketmine\command\ConsoleCommandSender;

abstract class Cheat {
	public $player;

	const AUTO_CLICKER = "autoClicker";
	const FLY = "fly";
	const KILL_AURA = "killAura";
	const REACH = "reach";
	const SPEED = "speed";
	const PHASE = "phase";

	const WARNING = 0;
	const KICK = 1;
	const BAN = 2;

	public abstract function set(CorePlayer $player);

	public function getPlayer() : CorePlayer {
		return $this->player;
	}

	public abstract function getId() : string;

	public abstract function getName() : string;

	public abstract function maxCheating() : int;

	public abstract function getPunishment();

	public abstract function getMainPunishment();

	public function historyCheck() : bool {
		$p = $this->getPlayer();

		$cheatHistory = $p->getCoreUser()->getCheatHistory();

		if($cheatHistory[$this->getId()] === $this->maxCheating()) {
			return true;
		}
		return false;
	}

	public abstract function onRun();

	public function final() {
		$p = $this->getPlayer();

		if($this->historyCheck()) {
			$punishment = $this->getMainPunishment();

			$p->getCoreUser()->setCheatHistory(Core::getInstance()->getAntiCheat()->getCheat($this->getId()), 0);
		} else {
			$punishment = $this->getPunishment();

			$p->getCoreUser()->addToCheatHistory(Core::getInstance()->getAntiCheat()->getCheat($this->getId()), 1);
		}
		switch($punishment) {
			case self::WARNING:
				$p->sendMessage(Core::ERROR_PREFIX . $punishment[1]);
				break;
			case self::KICK:
				Core::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(), "kick " . $p->getName() . " " . $punishment[1]);
				break;
			case self::BAN:
				if($punishment[2]) {
					$type = $punishment[1];

					if($type === "ip") {
						Core::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(), "banIp " . $p->getName() . " " . $punishment[1]);
					} else {
						$time = $type;
						Core::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(), "ban " . $p->getName() . " " . $punishment[2] . " " . $time);
					}
				}
				break;
		}
	}
}