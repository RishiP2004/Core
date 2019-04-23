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

use pocketmine\item\{
	ItemFactory,
	Item
};

class WitherSkeleton extends MonsterBase implements Collidable {
    use CollisionCheckingTrait, ItemHolderTrait;

    const NETWORK_ID = self::WITHER_SKELETON;

    public $width = 0.875, $height = 2.0;

    public function initEntity() : void {
		$this->mainHand = ItemFactory::get(Item::STONE_SWORD);

        parent::initEntity();
    }

    public function getName() : string {
        return "Wither Skeleton";
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