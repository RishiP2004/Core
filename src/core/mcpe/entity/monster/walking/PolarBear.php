<?php

namespace core\mcpe\entity\monster\walking;

use core\mcpe\entity\{
    CreatureBase,
    Collidable
};

use pocketmine\entity\Entity;

class PolarBear extends CreatureBase implements Collidable {
    const NETWORK_ID = self::POLAR_BEAR;

    public $width = 1.3, $height = 1.4;

    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
        return "Polar Bear";
    }

	public function entityBaseTick(int $tickDiff = 1) : bool {
		return parent::entityBaseTick($tickDiff);
	}

    public function onCollideWithEntity(Entity $entity) : void {
    }

    public function getDrops() : array {
        return parent::getDrops();
    }
}