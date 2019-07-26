<?php

declare(strict_types = 1);

namespace core\anticheat\cheat;

use core\Core;
use core\CorePlayer;

use core\anticheat\Cheats;

use pocketmine\command\ConsoleCommandSender;

abstract class Cheat implements Cheats {
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
	const BAN_IP = 3;

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

		if($p->getCoreUser()->getCheatHistoryFor($this) >= $this->maxCheating()) {
			return true;
		}
		return false;
	}

	public abstract function onRun() : void;

	public function final(int $amount = 1) : void {
		$p = $this->getPlayer();

		$p->getCoreUser()->addToCheatHistory(Core::getInstance()->getAntiCheat()->getCheat($this->getId()), $amount);

		if($this->historyCheck()) {
			$punishment = $this->getMainPunishment();

			$p->getCoreUser()->setCheatHistory(Core::getInstance()->getAntiCheat()->getCheat($this->getId()), 0);
		} else {
			$punishment = $this->getPunishment();
		}
		switch($punishment) {
			case self::WARNING:
				$p->sendMessage(Core::ERROR_PREFIX . $punishment[1]);
			break;
			case self::KICK:
				Core::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(), "kick " . $p->getName() . " " . $punishment[1]);
			break;
			case self::BAN:
				if(isset($punishment[2])) {
					Core::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(), "ban " . $p->getName() . " " . $punishment[1] . " " . $punishment[2]);
					return;
				}
				Core::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(), "ban " . $p->getName() . " " . $punishment[1]);
			break;
			case self::BAN_IP:
				if($punishment[2]) {
					Core::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(), "ban-ip " . $p->getName() . " " . $punishment[1] . " " . $punishment[2]);
					return;
				}
				Core::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(), "ban-ip " . $p->getName() . " " . $punishment[1]);
			break;
		}
	}
}