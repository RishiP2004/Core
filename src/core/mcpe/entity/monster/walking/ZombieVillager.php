<?php

namespace core\mcpe\entity\monster\walking;

use core\Core;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\item\Item;

class ZombieVillager extends Zombie {
    const NETWORK_ID = self::ZOMBIE_VILLAGER;

    public $width = 1.031, $height = 2.125;

    public function initEntity(CompoundTag $tag) : void {
        parent::initEntity($tag);
    }

    public function getName() : string {
        return "Zombie Villager";
    }

    public function getDrops() : array {
        $drops = [];

        if(Core::getInstance()->getMCPE()->drops()) {
            array_push($drops, Item::get(Item::ROTTEN_FLESH, 0, mt_rand(0, 2)));

            if(mt_rand(1, 1000) % 25 == 0){
                switch(mt_rand(1, 3)){
                    case 1:
                        array_push($drops, Item::get(Item::CARROT, 0, 1));
                    break;
                    case 2:
                        array_push($drops, Item::get(Item::POTATO, 0, 1));
                    break;
                    case 3:
                        array_push($drops, Item::get(Item::IRON_INGOT, 0, 1));
                    break;
                }
            }
        }
        return $drops;
    }
}