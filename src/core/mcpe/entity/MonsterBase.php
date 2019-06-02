<?php

declare(strict_types = 1);

namespace core\mcpe\entity;

use core\CorePlayer;

use pocketmine\event\entity\{
    EntityDamageEvent,
    EntityDamageByEntityEvent
};

use pocketmine\level\{
    Level,
    Position
};

use pocketmine\entity\Entity;

abstract class MonsterBase extends CreatureBase {
	public function initEntity() : void {
		parent::initEntity();
	}

	public function attack(EntityDamageEvent $source) : void {
		if($source instanceof EntityDamageByEntityEvent) {
			$this->setTarget($source->getDamager());
		}
		parent::attack($source);
	}

	public function entityBaseTick(int $tickDiff = 1) : bool {
		$hasUpdate = false;

		if($this->level->getDifficulty() <= Level::DIFFICULTY_PEACEFUL) {
			$this->flagForDespawn();
		}
		if($this->target === null) {
			foreach($this->hasSpawned as $player) {
				if($player->isSurvival() and $this->distance($player) <= 16 and $this->hasLineOfSight($player)) {
					$this->target = $player;
					$hasUpdate = true;
				}
			}
		} else if($this->target instanceof CorePlayer) {
			if($this->target->isCreative() or !$this->target->isAlive() or $this->distance($this->target) > 16 or !$this->hasLineOfSight($this->target)) {
				$this->target = null;
			}
		} else if($this->target instanceof CreatureBase) {
			if(!$this->target->isAlive() or $this->distance($this->target) > 16 or !$this->hasLineOfSight($this->target)) {
				$this->target = null;
			}
		}
		return parent::entityBaseTick($tickDiff) ? true : $hasUpdate;
	}

	protected function isTargetValid(?Position $target) : bool {
		if($target instanceof Entity) {
			if($target instanceof CorePlayer) {
				return !$target->isFlaggedForDespawn() and !$target->isClosed() and $target->isValid() and $target->isAlive() and $target->isSurvival();
			}
			return !$target->isFlaggedForDespawn() and !$target->isClosed() and $target->isValid() and $target->isAlive();
		} else {
			return $target !== null and $target->isValid();
		}
	}
}