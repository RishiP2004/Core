<?php

declare(strict_types = 1);

namespace core\mcpe\entity\monster\walking;

use core\mcpe\entity\{
    MonsterBase,
    Collidable,
    CollisionCheckingTrait,
    ItemHolderTrait,
    CreatureBase
};

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\level\Position;

use pocketmine\entity\Entity;

class Witch extends MonsterBase implements Collidable {
    use CollisionCheckingTrait, ItemHolderTrait;

    const NETWORK_ID = self::WITCH;

    public $width = 0.6, $height = 1.95;

    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
        return "Witch";
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

    public function getXpDropAmount() : int {
        return 5;
    }

    public function getDrops() : array {
		$drops = parent::getDrops();
		// TODO: chance drop potion
		return $drops;
    }
}