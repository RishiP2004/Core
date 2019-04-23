<?php

declare(strict_types = 1);

namespace core\stats\rank;

class Owner extends Rank {
    public function __construct() {
        parent::__construct("Owner");
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
        return new Manager();
    }

    public function getValue() : int {
        return self::STAFF;
    }

    public function getChatTime() : int {
    }
}