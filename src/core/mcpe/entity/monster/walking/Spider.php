<?php

namespace core\mcpe\entity\monster\walking;

use core\Core;

use pocketmine\entity\Monster;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\item\Item;

class Spider extends Monster {
    const NETWORK_ID = self::SPIDER;

    public function initEntity(CompoundTag $tag) : void {
        parent::initEntity($tag);

        $this->width = 2.062;
        $this->height = 0.781;
    }

    public function getName() : string {
        return "Spider";
    }

    public function getMaxHealth() : int {
        return 16;
    }

    public function getDrops() : array {
        $drops = [];

        if(Core::getInstance()->getMCPE()->drops()) {
            array_push($drops, Item::get(Item::STRING, 0, mt_rand(0, 2)));

            switch(mt_rand(0, 2)) {
                case 0:
                    array_push($drops, Item::get(Item::SPIDER_EYE, 0, 1));
                break;
            }
        }
        return $drops;
    }
}
