<?php

namespace core\mcpe\entity\animal\walking;

use core\Core;

use core\mcpe\entity\{
	CreatureBase,
	Collidable
};

use pocketmine\entity\Entity;

use pocketmine\item\Item;

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
        // TODO: Implement onCollideWithEntity() method.
    }

    public function getDrops() : array {
        if(Core::getInstance()->getMCPE()->drops()) {
            return [
                Item::get(Item::LEATHER, 0, mt_rand(1, 2))
            ];
        } else {
            return [];
        }
    }
}