<?php

namespace core\mcpe\entity\monster\walking;

use pocketmine\entity\Monster;

use pocketmine\nbt\tag\CompoundTag;

class Silverfish extends Monster {
    const NETWORK_ID = self::SILVERFISH;

    public function initEntity(CompoundTag $tag) : void {
        parent::initEntity($tag);

        $this->width = 1.094;
        $this->height = 0.483;
    }

    public function getName() : string {
        return "Silverfish";
    }

    public function getMaxHealth() : int {
        return 8;
    }

    public function getDrops() : array {
        return [];
    }
}