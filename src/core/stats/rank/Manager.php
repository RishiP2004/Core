<?php

declare(strict_types = 1);

namespace core\stats\rank;

class Manager extends Rank {
    public function __construct() {
        parent::__construct("Manager");
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
        return new Administrator();
    }

    public function getValue() : int {
        return self::STAFF;
    }

    public function getChatTime() : int {
    	return 0;
    }
}