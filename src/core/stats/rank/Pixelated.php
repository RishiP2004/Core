<?php

namespace core\stats\rank;

use pocketmine\utils\TextFormat;

class Pixelated extends Rank {
    public function __construct() {
        parent::__construct("Pixelated");

        $this->setPaidPrice(20);
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
        return new Hexcite();
    }

    public function getValue() : int {
        return self::PAID;
    }

    public function getChatTime() : int {
    }
}