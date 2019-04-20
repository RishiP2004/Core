<?php

namespace core\mcpe\entity\animal\walking;

use core\Core;
use core\CorePlayer;

use core\mcpe\entity\{
    AnimalBase,
    Collidable,
    Interactable
};

use pocketmine\entity\Entity;

use pocketmine\item\Item;

class Llama extends AnimalBase implements Collidable, Interactable {
    const NETWORK_ID = self::LLAMA;

    public $width = 0.9, $height = 1.87;

    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
        return "Llama";
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