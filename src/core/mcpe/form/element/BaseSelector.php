<?php

namespace core\mcpe\form\element;

use core\mcpe\form\FormValidationException;

abstract class BaseSelector extends CustomFormElement {
    protected $defaultOptionIndex;

    protected $options;

    public function __construct(string $name, string $text, array $options, int $defaultOptionIndex = 0) {
        parent::__construct($name, $text);
		
        $this->options = array_values($options);
		
        if(!isset($this->options[$defaultOptionIndex])) {
            throw new \InvalidArgumentException("No option at index $defaultOptionIndex, cannot set as default");
        }
        $this->defaultOptionIndex = $defaultOptionIndex;
    }

    public function validateValue($value) : void {
        if(!is_int($value)) {
            throw new FormValidationException("Expected int, got " . gettype($value));
        }
        if(!isset($this->options[$value])) {
            throw new FormValidationException("Option $value does not exist");
        }
    }

    public function getOption(int $index): ?string {
        return $this->options[$index] ?? null;
    }

    public function getDefaultOptionIndex(): int {
        return $this->defaultOptionIndex;
    }

    public function getDefaultOption() : string {
        return $this->options[$this->defaultOptionIndex];
    }

    public function getOptions() : array {
        return $this->options;
    }
}
