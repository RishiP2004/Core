<?php

namespace core\mcpe\entity\monster\walking;

use core\mcpe\entity\{
	MonsterBase,
	Collidable,
	CollisionCheckingTrait,
	CreatureBase
};

use pocketmine\level\Position;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\entity\Entity;

class Silverfish extends MonsterBase implements Collidable {
	use CollisionCheckingTrait;

    const NETWORK_ID = self::SILVERFISH;

	public $width = 1.094, $height = 0.438;

    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
        return "Silverfish";
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