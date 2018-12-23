<?php

namespace core\mcpe\form\element;

use core\mcpe\form\FormValidationException;

abstract class CustomFormElement implements \JsonSerializable {
    private $name, $text;

    public function __construct(string $name, string $text) {
        $this->name = $name;
        $this->text = $text;
    }

    abstract public function getType() : string;
	
    public function getName() : string {
        return $this->name;
    }

    public function getText() : string {
        return $this->text;
    }

    abstract public function validateValue($value) : void;

    final public function jsonSerialize() : array {
        $return = $this->serializeElementData();
        $return["type"] = $this->getType();
        $return["text"] = $this->getText();
        return $return;
    }

    abstract protected function serializeElementData() : array;
}
