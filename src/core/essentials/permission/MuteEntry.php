<?php

declare(strict_types = 1);

namespace core\essentials\permission;

class MuteEntry extends BanEntry {
    public function __construct(string $name) {
        parent::__construct($name);
        $this->setReason("Muted by an Operator");
    }
}