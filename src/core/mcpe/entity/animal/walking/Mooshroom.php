<?php

namespace core\mcpe\entity\animal\walking;

use core\Core;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\item\Item;

class Mooshroom extends Cow {
    const NETWORK_ID = self::MOOSHROOM;

    public $width = 1.781, $height = 1.875;

    public function initEntity(CompoundTag $tag) : void {
        parent::initEntity($tag);
    }

    public function getName() : string {
        return "Mooshroom";
    }

    public function entityBaseTick(int $tickDiff = 1) : bool {
        return parent::entityBaseTick($tickDiff);
    }

    public function getDrops() : array {
        $drops = [];

        if(Core::getInstance()->getMCPE()->drops()) {
            array_push($drops, Item::get(Item::LEATHER, 0, mt_rand(0, 2)));

            if($this->isOnFire()) {
                array_push($drops, Item::get(Item::COOKED_BEEF, 0, mt_rand(1, 3)));
            } else {
                array_push($drops, Item::get(Item::RAW_BEEF, 0, mt_rand(1, 3)));
            }
        }
        return $drops;
    }
}
