<?php

declare(strict_types = 1);

namespace core\stats\rank;

class Staff extends Rank {
    public function __construct() {
        parent::__construct("Staff");
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
        new YouTuber();
    }

    public function getValue() : int {
    }

    public function getChatTime() : int {
    }
}