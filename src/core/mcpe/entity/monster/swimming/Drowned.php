<?php

namespace core\mcpe\entity\monster\swimming;

use core\mcpe\entity\monster\walking\Zombie;
use core\mcpe\entity\{
	ItemHolderTrait,
	AgeableTrait,
	ClimbingTrait
};

class Drowned extends Zombie {
	use ItemHolderTrait, AgeableTrait, ClimbingTrait;

	public const NETWORK_ID = self::DROWNED;

	protected function applyGravity() : void {
		if(!$this->isUnderwater()) {
			parent::applyGravity();
		}
	}
}