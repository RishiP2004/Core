<?php

namespace core\mcpe\entity\monster\flying;

use core\mcpe\entity\{
	MonsterBase,
	Collidable,
	CollisionCheckingTrait
};

class EnderDragon extends MonsterBase implements Collidable {
	use CollisionCheckingTrait;

    const NETWORK_ID = self::ENDER_DRAGON;

    public $width = 2.5, $height = 1.0;

    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
        return "Ender Dragon";
    }

    public function entityBaseTick(int $tickDiff = 1) : bool {
        return parent::entityBaseTick($tickDiff);
    }

    public function getTarget() : ?Position {
        return $this->target;
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