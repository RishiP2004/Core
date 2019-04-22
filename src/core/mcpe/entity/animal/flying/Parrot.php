<?php

namespace core\mcpe\entity\animal\flying;

use core\CorePlayer;

use core\mcpe\entity\{
	AnimalBase,
	Collidable,
	Interactable
};

use pocketmine\entity\Entity;

class Parrot extends AnimalBase implements Collidable, Interactable {
	const NETWORK_ID = self::PARROT;

	public $width = 0.5;
	public $height = 0.9;
	
    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
        return "Parrot";
    }

	public function entityBaseTick(int $tickDiff = 1) : bool {
		return parent::entityBaseTick($tickDiff);
	}

	public function onCollideWithEntity(Entity $entity) : void {
	}

	public function onPlayerInteract(CorePlayer $player) : void {
	}

    public function getDrops() : array {
        return parent::getDrops();
    }
}
