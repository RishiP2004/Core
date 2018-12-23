<?php

namespace core\mcpe\form\element;

use core\mcpe\form\FormValidationException;

class Toggle extends CustomFormElement {
    private $default;

    public function __construct(string $name, string $text, bool $defaultValue = false) {
        parent::__construct($name, $text);
		
        $this->default = $defaultValue;
    }

    public function getType(): string {
        return "toggle";
    }

    public function getDefaultValue(): bool {
        return $this->default;
    }

    public function validateValue($value): void {
        if(!is_bool($value)) {
            throw new FormValidationException("Expected bool, got " . gettype($value));
        }
    }

    protected function serializeElementData(): array {
        return [
            "default" => $this->default
        ];
    }
}
