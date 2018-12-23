<?php

namespace core\stats\rank;

use pocketmine\utils\TextFormat;

class Athener extends Rank {
    public function __construct() {
        parent::__construct("Athener");

        $this->setPaidPrice(25);
    }

    public function getChatFormat() : string {
    }

    public function getNameTagFormat() : string {
    }

    public function getPermissions() : array {
    }

    public function getInheritance() : Rank {
        return new Pixelated();
    }

    public function getValue() : int {
        return self::PAID;
    }

    public function getChatTime() : int {
    }
}