<?php

namespace core\mcpe\entity\monster\walking;

use core\mcpe\entity\{
    CreatureBase,
    Collidable
};

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\entity\Entity;

class PolarBear extends CreatureBase implements Collidable {
    const NETWORK_ID = self::IRON_GOLEM;

    public $width = 1.3, $height = 1.4;

    public function initEntity(CompoundTag $tag) : void {
        parent::initEntity($tag);
    }

    public function getName() : string {
        return "Polar Bear";
    }

    public function onCollideWithEntity(Entity $entity) : void {
        // TODO: Implement onCollideWithEntity() method.
    }

    public function getDrops() : array {
        return parent::getDrops();
    }
}