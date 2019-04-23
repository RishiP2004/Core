<?php

declare(strict_types = 1);

namespace core\mcpe\form\element;

use pocketmine\form\FormValidationException;

class Toggle extends Element {
    protected $default = false;

    public function __construct(string $text, bool $default = false) {
        parent::__construct($text);

        $this->default = $default;
    }

    public function getType() : string {
        return "toggle";
    }

    public function getValue() : ?bool {
        return parent::getValue();
    }

    public function hasChanged() : bool {
        return $this->default !== $this->value;
    }

    public function getDefault() : bool {
        return $this->default;
    }

    public function serializeElementData() : array {
        return [
            "default" => $this->default
        ];
    }

    public function validate($value) : void {
        if(!is_bool($value)){
            throw new FormValidationException("Expected bool, got " . gettype($value));
        }
    }
}