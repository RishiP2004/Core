<?php

namespace core\mcpe\entity\animal\walking;

use core\Core;

use core\mcpe\entity\{
    CollisionCheckingTrait,
    CreatureBase
};

use pocketmine\entity\{
    Animal, 
	Rideable
};

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\level\Position;

use pocketmine\entity\Entity;

use pocketmine\item\Item;

class Horse extends Animal implements Rideable {
    use CollisionCheckingTrait;
    const NETWORK_ID = self::HORSE;

    public $width = 1.3, $height = 1.5;

    public function initEntity(CompoundTag $tag) : void {
        parent::initEntity($tag);
    }

    public function getName() : string {
        return "Horse";
    }

    public function entityBaseTick(int $tickDiff = 1) : bool {
        return parent::entityBaseTick($tickDiff);
    }

    public static function spawnMob(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
        // TODO: Implement spawnMob() method.
    }

    public static function spawnFromSpawner(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
        // TODO: Implement spawnFromSpawner() method.
    }

    public function onCollideWithEntity(Entity $entity) : void {
        // TODO: Implement onCollideWithEntity() method.
    }

    public function getDrops() : array {
        if(Core::getInstance()->getMCPE()->drops()) {
            return [
                Item::get(Item::LEATHER, 0, mt_rand(0, 2))
            ];
        } else {
            return [];
        }
    }
}
