<?php

namespace core\stats\rank;

use pocketmine\utils\TextFormat;

class Owner extends Rank {
    public function __construct() {
        parent::__construct("Owner");
    }

    public function getChatFormat() : string {
    }

    public function getNameTagFormat() : string {
    }

    public function getPermissions() : array {
    }

    public function getInheritance() : Rank {
        return new Manager();
    }

    public function getValue() : int {
        return self::STAFF;
    }

    public function getChatTime() : int {
    }
}