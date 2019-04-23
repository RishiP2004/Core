<?php

declare(strict_types = 1);

namespace core\mcpe\entity\monster\walking;

class Husk extends Zombie {
    public const NETWORK_ID = self::HUSK;

    public $width = 1.031, $height = 2.0;

    public function getName() : string {
        return "Husk";
    }
}