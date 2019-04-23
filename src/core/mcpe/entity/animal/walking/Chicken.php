<?php

declare(strict_types = 1);

namespace core\mcpe\entity\animal\walking;

use core\mcpe\entity\AnimalBase;

class Chicken extends AnimalBase {
	const NETWORK_ID = self::CHICKEN;

	public $width = 1, $height = 0.8;

    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
        return "Chicken";
    }

	public function entityBaseTick(int $tickDiff = 1) : bool {
		return parent::entityBaseTick($tickDiff);
	}

    public function getDrops() : array {
        return parent::getDrops();
	}
}
