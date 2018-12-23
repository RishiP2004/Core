<?php

namespace core\mcpe\entity\monster\swimming;

use core\mcpe\entity\{
    MonsterBase,
    Collidable,
    CollisionCheckingTrait,
    CreatureBase
};

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\level\Position;

use pocketmine\entity\Entity;

class Ghast extends MonsterBase implements Collidable {
    use CollisionCheckingTrait;
    const NETWORK_ID = self::GHAST;

    public $width = 4.5, $height = 4.5;

    public function initEntity(CompoundTag $tag) : void {
        parent::initEntity($tag);
    }

    public function getName() : string {
        return "Ghast";
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
        return parent::getDrops();
    }
}