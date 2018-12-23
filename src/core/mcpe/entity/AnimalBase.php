<?php

namespace core\mcpe\entity;

use pocketmine\entity\Ageable;

use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\nbt\tag\CompoundTag;

abstract class AnimalBase extends CreatureBase implements Ageable {
    use AgeableTrait, PanicableTrait;

	protected $growTime = 200;

	public function initEntity(CompoundTag $tag) : void {
		parent::initEntity($tag);
	}

	public function attack(EntityDamageEvent $source) : void {
		$this->setPanic();

		parent::attack($source);
	}

	public function entityBaseTick(int $tickDiff = 1) : bool {
		if($this->growTime -= $tickDiff <= 0) {
			$this->setBaby(false);
		}
		return parent::entityBaseTick($tickDiff);
	}
}