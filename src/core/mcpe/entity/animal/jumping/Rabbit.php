<?php

namespace core\mcpe\entity\animal\jumping;

use core\mcpe\entity\{
	AnimalBase,
	Collidable
};

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\entity\Entity;

class Rabbit extends AnimalBase implements Collidable {
	const NETWORK_ID = self::RABBIT;
	
	public $width = 0.4, $height = 0.5;

    public function initEntity(CompoundTag $tag) : void {
        parent::initEntity($tag);
    }

    public function getName() : string {
		return "Rabbit";
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