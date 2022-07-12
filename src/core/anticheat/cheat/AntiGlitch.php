<?php

declare(strict_types = 1);

namespace core\anticheat\cheat;

use core\Core;

use core\player\CorePlayer;

class AntiGlitch extends Cheat {
	private array $fenceGateInteracts = [];
	private int $fenceGateTime = 0;

	public function set(CorePlayer $player) : void {
		$this->player = $player;
	}

	public function getId() : string {
		return self::AUTO_CLICKER;
	}

	public function getName() : string {
		return "Anti Glitch";
	}

	public function maxCheating() : int {
		return 6;
	}

	public function getPunishment() : array {
		return [
			self::KICK,
			"Anti Glitch; Warning (" . $this->getPlayer()->getCoreUser()->getCheatHistory()[$this->getId()] . ")"
		];
	}

	public function getMainPunishment() : array {
		return [
			self::BAN,
			"Anti Glitch; Too many Chances given (" . $this->maxCheating() . ")",
			"8 days"
		];
	}

	public function onRun() : void {
		if (time() !== $this->fenceGateTime) {
			$this->fenceGateTime = time();
			$this->fenceGateInteracts = [];
		}
		if (!isset($this->fenceGateInteracts[$this->getPlayer()->getUniqueId()->getBytes()])) {
			$this->fenceGateInteracts[$this->getPlayer()->getUniqueId()->getBytes()] = 1;
		} else {
			++$this->fenceGateInteracts[$this->getPlayer()->getUniqueId()->getBytes()];
		}
		if ($this->fenceGateInteracts[$this->getPlayer()->getUniqueId()->getBytes()] > 8) {
			$this->fenceGateInteracts[$this->getPlayer()->getUniqueId()->getBytes()] = 0;

			$directionVector = $this->getPlayer()->getDirectionVector();

			$this->getPlayer()->knockback(0, -$directionVector->getX(), -$directionVector->getZ(), 0.5);
			$this->getPlayer()->setImmobile();

			Core::getInstance()->getScheduler()->scheduleDelayedTask(new class($this->getPlayer()) extends Task {
				private $player;

				public function __construct(HCFPlayer $player) {
					$this->player = $player;
				}

				public function onRun() : void {
					if ($this->player->isOnline()) {
						$this->player->setImmobile(false);
					}
				}
			}, 20);
		}
	}
}