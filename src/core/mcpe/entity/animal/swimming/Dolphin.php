<?php

namespace core\mcpe\entity\animal\swimming;

use core\mcpe\entity\{
	AnimalBase,
	Collidable
};

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\entity\Entity;

class Dolphin extends AnimalBase implements Collidable {
	const NETWORK_ID = self::DOLPHIN;
	
	public $width = 1.0, $height = 1.0;

	public function initEntity(CompoundTag $tag) : void {
		parent::initEntity($tag); 
	}
	
	public function getName() : string {
		return "Dolphin";
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