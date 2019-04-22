<?php

namespace core\mcpe\entity\monster\walking;

use core\mcpe\entity\{
    MonsterBase,
    Collidable,
    CollisionCheckingTrait,
    CreatureBase
};

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\level\Position;

use pocketmine\entity\Entity;

class Endermite extends MonsterBase implements Collidable {
    use CollisionCheckingTrait;

    const NETWORK_ID = self::ENDERMITE;

    public $width = 0.4, $height = 0.3;

    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
        return "Endermite";
    }

    public function entityBaseTick(int $tickDiff = 1) : bool {
        return parent::entityBaseTick($tickDiff);
    }

    public static function spawnMob(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
    }

    public static function spawnFromSpawner(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
    }

    public function onCollideWithEntity(Entity $entity) : void {
    }

    public function getDrops() : array {
        return parent::getDrops();
    }
}