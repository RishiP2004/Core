<?php

namespace core\mcpe\entity\animal\walking;

use core\Core;
use core\CorePlayer;

use core\mcpe\entity\{
    AnimalBase,
    Collidable,
    Interactable
};

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\entity\Entity;

use pocketmine\item\Item;

class Pig extends AnimalBase implements Collidable, Interactable {
    const NETWORK_ID = self::PIG;

    public $width = 1.5, $height = 1.0;

    public function initEntity(CompoundTag $tag) : void {
        $this->setMaxHealth(10);

        parent::initEntity($tag);
    }

    public function getName() : string {
        return "Pig";
    }


    public function entityBaseTick(int $tickDiff = 1) : bool {
        return parent::entityBaseTick($tickDiff);
    }

    public function onCollideWithEntity(Entity $entity) : void {
        // TODO: Implement onCollideWithEntity() method.
    }

    public function onPlayerInteract(CorePlayer $player) : void {
        // TODO: Implement onPlayerInteract() method.
    }

    public function getXpDropAmount() : int {
        //TODO: check for baby state
        return mt_rand(1, 3);
    }

    public function getDrops() : array {
        if(Core::getInstance()->getMCPE()->drops()) {
            if($this->isOnFire()) {
                return [
                    Item::get(Item::COOKED_PORKCHOP, 0, mt_rand(1, 3))
                ];
            } else {
                return [
                    Item::get(Item::RAW_PORKCHOP, 0, mt_rand(1, 3))
                ];
            }
        } else {
            return [];
        }
    }
}