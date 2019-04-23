<?php

declare(strict_types = 1);

namespace core\stats\rank;

class Universal extends Rank {
    public function __construct() {
        parent::__construct("Universal");

        $this->setPaidPrice(10);
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
        return new Eonive();
    }

    public function getValue() : int {
        return self::PAID;
    }

    public function getChatTime() : int {
    }
}