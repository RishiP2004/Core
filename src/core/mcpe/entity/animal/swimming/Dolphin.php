<?php

namespace core\mcpe\entity\animal\swimming;

use core\mcpe\entity\{
	AnimalBase,
	Collidable
};

use pocketmine\entity\Entity;

class Dolphin extends AnimalBase implements Collidable {
	const NETWORK_ID = self::DOLPHIN;
	
	public $width = 1.0, $height = 1.0;

	public function initEntity() : void {
		parent::initEntity();
	}
	
	public function getName() : string {
		return "Dolphin";
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