<?php

namespace core\mcpe\entity\monster\walking;

use pocketmine\nbt\tag\CompoundTag;

class CaveSpider extends Spider {
    const NETWORK_ID = self::SNOW_GOLEM;

    public $width = 1.438, $height = 0.547;

    public function initEntity(CompoundTag $tag) : void {
        parent::initEntity($tag);
    }

    public function getName() : string {
        return "Cave Spider";
    }
}