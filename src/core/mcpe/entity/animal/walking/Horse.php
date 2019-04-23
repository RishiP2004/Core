<?php

declare(strict_types = 1);

namespace core\mcpe\entity\animal\walking;

use core\mcpe\entity\{
	AnimalBase,
	Collidable,
	CollisionCheckingTrait,
	CreatureBase
};

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\level\Position;

use pocketmine\entity\Entity;

class Horse extends AnimalBase implements Collidable {
    use CollisionCheckingTrait;
    const NETWORK_ID = self::HORSE;

    public $width = 1.3, $height = 1.5;

    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
        return "Horse";
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
