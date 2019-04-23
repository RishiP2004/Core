<?php

declare(strict_types = 1);

namespace core\essentials\permission;

class BlockEntry extends BanEntry {
    public function __construct(string $name) {
        parent::__construct($name);
        $this->setReason("Blocked by an Operator");
    }
}