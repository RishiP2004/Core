<?php

declare(strict_types = 1);

namespace core\mcpe\entity\monster\walking;

class Stray extends Skeleton {
    const NETWORK_ID = self::STRAY;

    public function getName() : string {
        return "Stray";
    }
}