<?php

namespace core\mcpe\entity\animal\walking;

use core\mcpe\entity\{
    AnimalBase,
    Collidable
};

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\entity\Entity;

class Sheep extends AnimalBase implements Collidable{
    const NETWORK_ID = self::SHEEP;

    public $width = 1.2, $height = 0.6;

    public function initEntity(CompoundTag $tag) : void {
        parent::initEntity($tag);
    }

    public function getName() : string {
        return "Sheep";
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