<?php

declare(strict_types = 1);

namespace core\mcpe\entity\monster\walking;

use core\mcpe\entity\{
    MonsterBase,
    CreatureBase
};

use pocketmine\level\Position;

use pocketmine\nbt\tag\CompoundTag;

class ZombieHorse extends MonsterBase {
    public const NETWORK_ID = self::ZOMBIE_HORSE;

    public $width = 1.3, $height = 1.5;

    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
        return "Zombie Horse";
    }

    public function entityBaseTick(int $tickDiff = 1) : bool {
        return parent::entityBaseTick($tickDiff);
    }

    public static function spawnMob(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
    }

    public function getDrops() : array {
        return parent::getDrops();
    }
}