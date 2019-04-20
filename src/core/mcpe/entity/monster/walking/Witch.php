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

use pocketmine\item\Item;

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
        // TODO: Implement spawnMob() method.
    }

    public static function spawnFromSpawner(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
        // TODO: Implement spawnFromSpawner() method.
    }

    public function onCollideWithEntity(Entity $entity) : void {
        // TODO: Implement onCollideWithEntity() method.
    }

    public function getXpDropAmount() : int {
        return 5;
    }

    public function getDrops() : array {
        $drops = [];

        if(Core::getInstance()->getMCPE()->drops()) {
            if(mt_rand(1, 1000) % 25 == 0) {
                switch(mt_rand(1, 3)) {
                    case 1:
                        array_push($drops, Item::get(Item::GLASS_BOTTLE, 0, 1));
                    break;
                    case 2:
                        array_push($drops, Item::get(Item::GLOWSTONE_DUST, 0, 1));
                    break;
                    case 3:
                        array_push($drops, Item::get(Item::GUNPOWDER, 0, 1));
                    break;
                    case 4:
                        array_push($drops, Item::get(Item::REDSTONE, 0, 1));
                    break;
                    case 5:
                        array_push($drops, Item::get(Item::SPIDER_EYE, 0, 1));
                    break;
                    case 6:
                        array_push($drops, Item::get(Item::SUGAR, 0, 1));
                    break;
                    case 7:
                        array_push($drops, Item::get(Item::STICK, 0, 1));
                    break;
                }
            }
        }
        return $drops;
    }
}