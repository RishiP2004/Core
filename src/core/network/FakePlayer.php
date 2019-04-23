<?php

declare(strict_types = 1);

namespace core\network;

class FakePlayer {
    private $name = "";

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function getName() : string {
        return $this->name;
    }

    public function isOnline() : bool {
        return false;
    }
}