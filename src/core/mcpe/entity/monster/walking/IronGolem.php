<?php

declare(strict_types = 1);

namespace core\mcpe\entity\monster\walking;

use core\mcpe\entity\{
    CreatureBase,
    Collidable
};

use pocketmine\entity\Entity;

class IronGolem extends CreatureBase implements Collidable {
    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
        return "Iron Golem";
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