<?php

namespace core\mcpe\entity\monster\walking;

use core\mcpe\entity\{
    CreatureBase,
    Collidable
};

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\entity\Entity;

class Wolf extends CreatureBase implements Collidable {
    const NETWORK_ID = self::WOLF;


    public $width = 1.2, $height = 0.969;

    public function initEntity(CompoundTag $tag) : void {
        parent::initEntity($tag);
    }

    public function getName() : string {
        return "Wolf";
    }

    public function entityBaseTick(int $tickDiff = 1) : bool {
        return parent::entityBaseTick($tickDiff); // TODO: Change the autogenerated stub
    }

    public function onCollideWithEntity(Entity $entity) : void {
        // TODO: Implement onCollideWithEntity() method.
    }

    public function getDrops() : array {
        return parent::getDrops();
    }
}