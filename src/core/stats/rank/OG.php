<?php

declare(strict_types = 1);

namespace core\stats\rank;

class OG extends Rank {
    public function __construct() {
        parent::__construct("OG");

        $this->setFreePrice(10000);
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
        return new Player();
    }

    public function getValue() : int {
        return self::FREE;
    }

    public function getChatTime() : int {
    	return 0;
    }
}