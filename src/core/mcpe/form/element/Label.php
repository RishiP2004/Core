<?php

declare(strict_types = 1);

namespace core\mcpe\form\element;

use pocketmine\form\FormValidationException;

class Label extends Element {
    public function getType() : string {
        return "label";
    }

    public function serializeElementData() : array {
        return [];
    }

    public function validate($value) : void {
        if($value !== null) {
            throw new FormValidationException("Expected null, got " . gettype($value));
        }
    }
}
