<?php

declare(strict_types = 1);

namespace core\mcpe\entity\monster\walking;

use core\mcpe\entity\{
    CreatureBase,
    Collidable
};

use pocketmine\entity\Entity;

class Wolf extends CreatureBase implements Collidable {
    const NETWORK_ID = self::WOLF;

    public $width = 1.2, $height = 0.969;

    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
        return "Wolf";
    }

    public function entityBaseTick(int $tickDiff = 1) : bool {
        return parent::entityBaseTick($tickDiff);
    }

    public function onCollideWithEntity(Entity $entity) : void {
    }

    public function getDrops() : array {
        return parent::getDrops();
    }
}