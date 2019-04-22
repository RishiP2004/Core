<?php

namespace core\mcpe\entity\animal\jumping;

use core\mcpe\entity\{
	AnimalBase,
	Collidable
};

use pocketmine\entity\Entity;

class Rabbit extends AnimalBase implements Collidable {
	const NETWORK_ID = self::RABBIT;
	
	public $width = 0.4, $height = 0.5;

    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
		return "Rabbit";
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