<?php

declare(strict_types = 1);

namespace core\anticheat\cheat;

use core\Core;

use core\player\CorePlayer;

use core\anticheat\Cheats;

use pocketmine\console\ConsoleCommandSender;

use pocketmine\lang\Language;

use pocketmine\Server;
//OOP for punishments?..
abstract class Cheat implements Cheats {
	public CorePlayer $player;

	const AUTO_CLICKER = "autoClicker";
	const GLITCH = "glitch";
	const FLY = "fly";
	const KILL_AURA = "killAura";
	const REACH = "reach";
	const SPEED = "speed";
	const PHASE = "phase";

	const WARNING = 0;
	const KICK = 1;
	const BAN = 2;
	const BAN_IP = 3;

	public abstract function set(CorePlayer $player) : void;

	public function getPlayer() : CorePlayer {
		return $this->player;
	}

	public abstract function getId() : string;

	public abstract function getName() : string;

	public abstract function maxCheating() : int;

	public abstract function getPunishment() : array;

	public abstract function getMainPunishment() : array;

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

		$p->getCoreUser()->addToCheatHistory($this, $amount);

		if($this->historyCheck()) {
			$punishment = $this->getMainPunishment();

			$p->getCoreUser()->setCheatHistory($this, 0);
		} else {
			$punishment = $this->getPunishment();
		}
		switch($punishment) {
			case self::WARNING:
				$p->sendMessage(Core::ERROR_PREFIX . $punishment[1]);
			break;
			case self::KICK:
				Core::getInstance()->getServer()->getCommandMap()->dispatch(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), "kick " . $p->getName() . " " . $punishment[1]);
			break;
			case self::BAN:
				if(isset($punishment[2])) {
					Core::getInstance()->getServer()->getCommandMap()->dispatch(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), "ban " . $p->getName() . " " . $punishment[1] . " " . $punishment[2]);
					return;
				}
				Core::getInstance()->getServer()->getCommandMap()->dispatch(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), "ban " . $p->getName() . " " . $punishment[1]);
			break;
			case self::BAN_IP:
				if($punishment[2]) {
					Core::getInstance()->getServer()->getCommandMap()->dispatch(new ConsoleCommandSender(Server::getInstance(), Language::FALLBACK_LANGUAGE), "ban-ip " . $p->getName() . " " . $punishment[1] . " " . $punishment[2]);
					return;
				}
				Core::getInstance()->getServer()->getCommandMap()->dispatch(new ConsoleCommandSender(Server::getInstance(), Language::FALLBACK_LANGUAGE), "ban-ip " . $p->getName() . " " . $punishment[1]);
			break;
		}
	}
}