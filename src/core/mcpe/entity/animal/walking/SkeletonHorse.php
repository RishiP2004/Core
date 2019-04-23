<?php

declare(strict_types = 1);

namespace core\mcpe\entity\animal\walking;

use core\mcpe\entity\{
	CreatureBase,
	Collidable
};

use pocketmine\entity\Entity;

class SkeletonHorse extends CreatureBase implements Collidable {
    const NETWORK_ID = self::SKELETON_HORSE;

	public $width = 1.3, $height = 1.5;

    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
        return "Skeleton Horse";
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