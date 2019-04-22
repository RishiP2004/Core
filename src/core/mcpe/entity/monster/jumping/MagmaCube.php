<?php

namespace core\mcpe\entity\monster\jumping;

use core\mcpe\entity\CreatureBase;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\level\Position;

class MagmaCube extends Slime {
    const NETWORK_ID = self::MAGMA_CUBE;

    public $width = 1.2, $height = 1.2;

    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
        return "Magma Cube";
    }

    public static function spawnMob(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
    }

    public static function spawnFromSpawner(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
    }
}