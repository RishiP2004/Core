<?php

namespace core\stats\rank;

use pocketmine\utils\TextFormat;

class Player extends Rank {
    public function __construct() {
        parent::__construct("Player");
    }

    public function getChatFormat() : string {
    }

    public function getNameTagFormat() : string {
    }

    public function getPermissions() : array {
    }

    public function getInheritance() : ?Rank {
        return null;
    }

    public function getValue() : int {
        return self::DEFAULT;
    }

    public function getChatTime() : int {
    }
}