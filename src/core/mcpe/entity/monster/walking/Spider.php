<?php

declare(strict_types = 1);

namespace core\mcpe\entity\monster\walking;

use core\mcpe\entity\{
	MonsterBase,
	ClimbingTrait,
	CreatureBase
};

use pocketmine\level\Position;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\entity\Entity;

class Spider extends MonsterBase {
	use ClimbingTrait;

	public const NETWORK_ID = self::SPIDER;
	public $width = 2.062;
	public $height = 0.781;

	public function initEntity() : void {
		$this->setCanClimbWalls();
		parent::initEntity();
	}

	public function getName() : string {
		return "Spider";
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
