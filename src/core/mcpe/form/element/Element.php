<?php

namespace core\mcpe\form\element;

use pocketmine\form\FormValidationException;

abstract class Element implements \JsonSerializable {
    protected $text = "";
    /** @var null|mixed */
    protected $value;

    public function __construct(string $text) {
        $this->text = $text;
    }

    abstract public function getType() : ?string;

    public function getValue() : ?int {
        return $this->value;
    }
    /** @var int|null $value */
    public function setValue($value) {
        $this->value = $value;
    }

    final public function jsonSerialize() : array {
        $array = [
            "text" => $this->getText()
        ];

        if($this->getType() !== null) {
            $array["type"] = $this->getType();
        }
        return $array + $this->serializeElementData();
    }

    public function getText() : string {
        return $this->text;
    }

    abstract public function serializeElementData() : array;

    public function validate($value) : void {
        if(!is_int($value)) {
            throw new FormValidationException("Expected int, got " . gettype($value));
        }
    }
}
