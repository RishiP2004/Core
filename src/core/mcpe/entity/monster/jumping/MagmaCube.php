<?php

namespace core\mcpe\entity\monster\jumping;

use core\Core;

use core\mcpe\entity\CreatureBase;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\level\Position;

use pocketmine\item\Item;

class MagmaCube extends Slime {
    const NETWORK_ID = self::MAGMA_CUBE;

    public $width = 1.2, $height = 1.2;

    public function initEntity(CompoundTag $tag) : void {
        parent::initEntity($tag);
    }

    public function getName() : string {
        return "Magma Cube";
    }

    public static function spawnMob(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
        // TODO: Implement spawnMob() method.
    }

    public static function spawnFromSpawner(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
        // TODO: Implement spawnFromSpawner() method.
    }

    public function getDrops() : array {
        $drops = [];

        if(Core::getInstance()->getMCPE()->drops()) {
            switch(mt_rand(0, 1)){
                case 0:
                    $drops[] = Item::get(Item::NETHERRACK, 0, 1);
                break;
                case 1:
                    $drops[] = Item::get(Item::MAGMA_CREAM, 0, 1);
                break;
            }
        }
        return $drops;
    }
}