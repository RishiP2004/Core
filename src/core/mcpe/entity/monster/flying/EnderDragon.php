<?php

namespace core\mcpe\entity\monster\flying;

use core\mcpe\entity\{
	MonsterBase,
	Collidable,
	CollisionCheckingTrait,
	CreatureBase
};

use pocketmine\level\Position;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\entity\Entity;

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
    }

    public static function spawnFromSpawner(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
    }

    public function onCollideWithEntity(Entity $entity) : void {
    }

    public function getDrops() : array {
        return parent::getDrops();
    }
}