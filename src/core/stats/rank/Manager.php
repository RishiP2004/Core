<?php

namespace core\stats\rank;

use pocketmine\utils\TextFormat;

class Manager extends Rank {
    public function __construct() {
        parent::__construct("Manager");
    }

    public function getChatFormat() : string {
    }

    public function getNameTagFormat() : string {
    }

    public function getPermissions() : array {
    }

    public function getInheritance() : Rank {
        return new Administrator();
    }

    public function getValue() : int {
        return self::STAFF;
    }

    public function getChatTime() : int {
    }
}