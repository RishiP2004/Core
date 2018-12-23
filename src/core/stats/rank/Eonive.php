<?php

namespace core\stats\rank;

use pocketmine\utils\TextFormat;

class Eonive extends Rank {
    public function __construct() {
        parent::__construct("Eonive");

        $this->setPaidPrice(5);
    }

    public function getChatFormat() : string {
    }

    public function getNameTagFormat() : string {
    }

    public function getPermissions() : array {
    }

    public function getInheritance() : Rank {
        return new Athener();
    }

    public function getValue() : int {
        return self::PAID;
    }

    public function getChatTime() : int {
    }
}