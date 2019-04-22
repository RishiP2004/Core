<?php

namespace core\mcpe\entity\animal\walking;

use core\CorePlayer;

use core\mcpe\entity\{
    AnimalBase,
    Collidable,
    Interactable
};

use pocketmine\entity\Entity;

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
    }

    public function onPlayerInteract(CorePlayer $player) : void {
    }

    public function getDrops() : array {
        return parent::getDrops();
    }
}