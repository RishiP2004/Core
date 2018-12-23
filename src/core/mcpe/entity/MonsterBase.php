<?php

namespace core\mcpe\entity;

use core\CorePlayer;

use pocketmine\nbt\tag\CompoundTag;

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
	public function initEntity(CompoundTag $tag) : void {
		parent::initEntity($tag);
	}

	public function attack(EntityDamageEvent $source) : void {
		if($source instanceof EntityDamageByEntityEvent) {
			$this->setTarget($source->getDamager());
		}
		parent::attack($source);
	}

	public function entityBaseTick(int $tickDiff = 1) : bool {
		if($this->level->getDifficulty() <= Level::DIFFICULTY_PEACEFUL) {
			$this->flagForDespawn();
		}
		return parent::entityBaseTick($tickDiff);
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