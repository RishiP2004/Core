<?php

declare(strict_types = 1);

namespace core\stats\rank;

class Player extends Rank {
    public function __construct() {
        parent::__construct("Player");
    }

    public function getFormat() : string {
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