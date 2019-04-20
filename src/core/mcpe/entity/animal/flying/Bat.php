<?php

namespace core\mcpe\entity\animal\flying;

use core\mcpe\entity\{
	AnimalBase,
	Collidable
};

use pocketmine\entity\Entity;

class Bat extends AnimalBase implements Collidable {
	const NETWORK_ID = self::BAT;

	public $width = 0.484, $height = 0.5;

    public function initEntity() : void {
        parent::initEntity();
    }

	public function getName() : string {
		return "Bat";
	}

	public function entityBaseTick(int $tickDiff = 1) : bool {
		return parent::entityBaseTick($tickDiff);
	}
	
	public function onCollideWithEntity(Entity $entity) : void {
		// TODO: Implement onCollideWithEntity() method.
	}

    public function getDrops() : array {
        return parent::getDrops();
    }
}
