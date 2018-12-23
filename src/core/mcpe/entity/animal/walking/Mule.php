<?php

namespace core\mcpe\entity\animal\walking;

use core\Core;
use core\CorePlayer;

use core\mcpe\entity\Interactable;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\item\Item;

class Mule extends Donkey implements Interactable {
    const NETWORK_ID = self::MULE;

    public $width = 1.2, $height = 1.562;

    public function initEntity(CompoundTag $tag) : void {
        parent::initEntity($tag);
    }

    public function getName() : string {
        return "Mule";
    }

    public function entityBaseTick(int $tickDiff = 1) : bool {
        return parent::entityBaseTick($tickDiff);
    }

    public function onPlayerInteract(CorePlayer $player) : void {
        // TODO: Implement onPlayerInteract() method.
    }

    public function getDrops() : array {
        if(Core::getInstance()->getMCPE()->drops()) {
            return [
                Item::get(Item::LEATHER, 0, mt_rand(0, 2))
            ];
        } else {
            return [];
        }
    }
}
