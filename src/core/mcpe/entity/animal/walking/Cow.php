<?php

declare(strict_types = 1);

namespace core\mcpe\entity\animal\walking;

use core\CorePlayer;

use core\mcpe\entity\{
	AnimalBase,
	Collidable,
	Interactable
};

use pocketmine\entity\Entity;

class Cow extends AnimalBase implements Collidable, Interactable {
    const NETWORK_ID = self::COW;

	public $width = 1.5, $height = 1.2, $eyeHeight = 1;
	
    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
        return "Cow";
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
