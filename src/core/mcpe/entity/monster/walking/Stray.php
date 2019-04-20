<?php

namespace core\mcpe\entity\monster\walking;

use core\Core;

use pocketmine\item\Item;

class Stray extends Skeleton {
    const NETWORK_ID = self::STRAY;

    public function getName() : string {
        return "Stray";
    }

    public function getDrops() : array {
        $drops = [];

        if(Core::getInstance()->getMCPE()->drops()) {
            array_push($drops, Item::get(Item::ARROW, 0, mt_rand(0, 2)));
            array_push($drops, Item::get(Item::BONE, 0, mt_rand(0, 2)));
        }
        return $drops;
    }
}