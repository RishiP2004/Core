<?php

declare(strict_types = 1);

namespace core\stats\rank;

class YouTuber extends Rank {
    public function __construct() {
        parent::__construct("YouTuber");
    }

    public function getFormat() : string {
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