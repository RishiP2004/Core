<?php

namespace core\mcpe\entity\monster\walking;

use core\Core;

use pocketmine\entity\Monster;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\item\Item;

class Shulker extends Monster {
    const NETWORK_ID = self::SNOW_GOLEM;

    public function initEntity(CompoundTag $tag) : void {
        parent::initEntity($tag);

        $this->width = 1.0;
        $this->height = 1.0;
    }

    public function getName() : string {
        return "Shulker";
    }

    public function getMaxHealth() : int {
        return 30;
    }

    public function getDrops() : array {
        $drops = [];

        if(Core::getInstance()->getMCPE()->drops()) {
            switch(mt_rand(0, 1)) {
                case 0:
                    array_push($drops, Item::get(Item::SHULKER_SHELL, 0, 1));
                break;
            }
        }
        return $drops;
    }
}