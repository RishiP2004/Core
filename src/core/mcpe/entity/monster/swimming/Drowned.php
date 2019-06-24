<?php

namespace core\mcpe\entity\monster\swimming;

use core\mcpe\entity\monster\walking\Zombie;

class Drowned extends Zombie {
	public const NETWORK_ID = self::DROWNED;

	protected function applyGravity() : void {
		if(!$this->isUnderwater()) {
			parent::applyGravity();
		}
	}
}