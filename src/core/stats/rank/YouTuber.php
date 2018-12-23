<?php

namespace core\stats\rank;

use pocketmine\utils\TextFormat;

class YouTuber extends Rank {
    public function __construct() {
        parent::__construct("YouTuber");
    }

    public function getChatFormat() : string {
        return "";
    }

    public function getNameTagFormat() : string {
        return "";
    }

    public function getPermissions() : array {
        return [];
    }

    public function getInheritance() : ?Rank {
        return new Athener();
    }

    public function getValue() : int {
        return self::STAFF;
    }

    public function getChatTime() : int {
        return 0;
    }
}