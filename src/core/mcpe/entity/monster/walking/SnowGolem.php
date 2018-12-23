<?php

namespace core\mcpe\entity\monster\walking;

use core\Core;

use pocketmine\entity\Monster;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\item\Item;

class SnowGolem extends Monster {
    const NETWORK_ID = self::SNOW_GOLEM;

    public function initEntity(CompoundTag $tag) : void {
        parent::initEntity($tag);

        $this->width = 1.281;
        $this->height = 1.875;
    }

    public function getName() : string {
        return "Snow Golem";
    }

    public function getMaxHealth() : int {
        return 4;
    }

    public function getDrops() : array {
        if(Core::getInstance()->getMCPE()->drops()) {
            return [
                Item::get(Item::SNOWBALL, 0, mt_rand(0, 15))
            ];
        } else {
            return [];
        }
    }
}