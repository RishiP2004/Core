<?php

namespace core\mcpe\entity\monster\walking;

use core\Core;

use core\mcpe\entity\{
    CreatureBase,
    Collidable
};

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\entity\Entity;

use pocketmine\item\Item;

class IronGolem extends CreatureBase implements Collidable {
    const NETWORK_ID = self::IRON_GOLEM;

    public $width = 2.688, $height = 1.625;

    public function initEntity(CompoundTag $tag) : void {
        parent::initEntity($tag);
    }

    public function getName() : string {
        return "Iron Golem";
    }

    public function onCollideWithEntity(Entity $entity) : void {
        // TODO: Implement onCollideWithEntity() method.
    }

    public function getDrops() : array {
        $drops = [];

        if(Core::getInstance()->getMCPE()->drops()) {
            array_push($drops, Item::get(Item::IRON_INGOT, 0, mt_rand(3, 5)));
            array_push($drops, Item::get(Item::POPPY, 0, mt_rand(0, 2)));
        }
        return $drops;
    }
}