<?php

declare(strict_types = 1);

namespace core\essential\permission;

class BanEntry extends \pocketmine\permission\BanEntry {
    public function __construct(string $name) {
        parent::__construct($name);
        $this->setReason("Banned by an Operator");
    }
}