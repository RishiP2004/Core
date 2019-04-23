<?php

declare(strict_types = 1);

namespace core\mcpe\entity\object;

use pocketmine\entity\Entity;

class TripodCamera extends Entity {
	public function initEntity() : void {
		parent::initEntity();
	}

	public function getName() : string {
		return "Tripod Camera";
	}

	public function entityBaseTick(int $tickDiff = 1) : bool {
		return parent::entityBaseTick($tickDiff);
	}
}