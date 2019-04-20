<?php

namespace core\mcpe\entity\monster\walking;

use core\Core;

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
        // TODO: Implement spawnMob() method.
    }

    public static function spawnFromSpawner(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
        // TODO: Implement spawnFromSpawner() method.
    }

    public function onCollideWithEntity(Entity $entity) : void {
        // TODO: Implement onCollideWithEntity() method.
    }

    public function getDrops() : array {
        $drops = [];

        if(Core::getInstance()->getMCPE()->drops()) {
            array_push($drops, Item::get(Item::COAL, 0, mt_rand(0, 1)));
            array_push($drops, Item::get(Item::BONE, 0, mt_rand(0, 2)));

            switch(mt_rand(0, 8)) {
                case 1:
                    array_push($drops, Item::get(Item::MOB_HEAD, 1, mt_rand(0, 2)));
                break;
            }
        }
        return $drops;
    }
}