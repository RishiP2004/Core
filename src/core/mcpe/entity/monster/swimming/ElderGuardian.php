<?php

namespace core\mcpe\entity\monster\swimming;

use pocketmine\nbt\tag\CompoundTag;

class ElderGuardian extends Guardian {
    const NETWORK_ID = self::ELDER_GUARDIAN;

    public $width = 1.9975, $height = 1.9975;

    public function initEntity(CompoundTag $tag) : void {
        parent::initEntity($tag);
    }

    public function getName() : string {
        return "Elder Guardian";
    }

    public function getDrops() : array {
        return parent::getDrops();
    }
}
