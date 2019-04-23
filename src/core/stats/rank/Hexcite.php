<?php

declare(strict_types = 1);

namespace core\stats\rank;

class Hexcite extends Rank {
    public function __construct() {
        parent::__construct("Hexcite");

        $this->setFreePrice(5000);
    }

    public function getFormat() : string {
    }

    public function getChatFormat() : string {
    }

    public function getNameTagFormat() : string {
    }

    public function getPermissions() : array {
    }

    public function getInheritance() : Rank {
        return new Universal();
    }

    public function getValue() : int {
        return self::PAID;
    }

    public function getChatTime() : int {
    }
}