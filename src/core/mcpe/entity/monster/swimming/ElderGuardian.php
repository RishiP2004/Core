<?php

declare(strict_types = 1);

namespace core\mcpe\entity\monster\swimming;

class ElderGuardian extends Guardian {
    const NETWORK_ID = self::ELDER_GUARDIAN;

    public $width = 1.9975, $height = 1.9975;

    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
        return "Elder Guardian";
    }

    public function getDrops() : array {
        return parent::getDrops();
    }
}
