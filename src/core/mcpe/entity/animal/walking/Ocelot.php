<?php

namespace core\mcpe\entity\animal\walking;

use core\CorePlayer;

use core\mcpe\entity\{
    AnimalBase,
    Collidable,
    Interactable
};

use pocketmine\entity\Entity;

class Ocelot extends AnimalBase implements Collidable, Interactable {
    const NETWORK_ID = self::OCELOT;

    public $width = 0.8, $height = 0.8;

    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
        return "Ocelot";
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
        return parent::getDrops();
    }
}