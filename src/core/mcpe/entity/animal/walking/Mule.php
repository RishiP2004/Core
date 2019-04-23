<?php

declare(strict_types = 1);

namespace core\mcpe\entity\animal\walking;

use core\CorePlayer;

use core\mcpe\entity\Interactable;

class Mule extends Donkey implements Interactable {
    const NETWORK_ID = self::MULE;

    public $width = 1.2, $height = 1.562;

    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
        return "Mule";
    }

    public function entityBaseTick(int $tickDiff = 1) : bool {
        return parent::entityBaseTick($tickDiff);
    }

    public function onPlayerInteract(CorePlayer $player) : void {
    }

    public function getDrops() : array {
        return parent::getDrops();
    }
}
