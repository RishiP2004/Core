<?php

declare(strict_types = 1);

namespace core\mcpe\entity;

use pocketmine\entity\Ageable;

use pocketmine\event\entity\EntityDamageEvent;

abstract class AnimalBase extends CreatureBase implements Ageable {
    use AgeableTrait, PanicableTrait;

	protected $growTime = 200;

	public function initEntity() : void {
		parent::initEntity();
	}

	public function attack(EntityDamageEvent $source) : void {
		$this->setPanic();

		parent::attack($source);
	}

	public function entityBaseTick(int $tickDiff = 1) : bool {
		if($this->growTime -= $tickDiff <= 0) {
			$this->setBaby(false);
		}
		//TODO: Normal animal movements
		return parent::entityBaseTick($tickDiff);
	}
}