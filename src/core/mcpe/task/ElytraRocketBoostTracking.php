<?php

declare(strict_types = 1);

namespace core\mcpe\task;

use core\Core;

use core\mcpe\level\particle\RocketParticle;

use pocketmine\Player;
use pocketmine\scheduler\Task;

class ElytraRocketBoostTrackingTask extends Task {
	protected $player;

	protected $count;

	private $internalCount = 1;

	public function __construct(Player $player, int $count) {
		$this->player = $player;
		$this->count = $count;
	}

	public function onRun(int $currentTick) {
		if($this->internalCount <= $this->count) {
			$this->player->getLevel()->addParticle(new RocketParticle($this->player->asVector3()->add(
				$this->player->width / 2 + mt_rand(-100, 100) / 500,
				$this->player->height / 2 + mt_rand(-100, 100) / 500,
				$this->player->width / 2 + mt_rand(-100, 100) / 500
			)));
			$this->internalCount++;
		} else {
			Core::getInstance()->getScheduler()->cancelTask($this->getTaskId());
		}
	}
}