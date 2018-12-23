<?php

namespace core\mcpe\form;

use pocketmine\form\Form;

abstract class BaseForm implements Form {
    protected $title;
	
    public function __construct(string $title) {
        $this->title = $title;
    }

    public function getTitle(): string {
        return $this->title ?? "";
    }

    final public function jsonSerialize(): array {
        $return = $this->serializeFormData();
        $return["type"] = $this->getType();
        $return["title"] = $this->getTitle();
        return $return;
    }

    abstract protected function getType() : string;

    abstract protected function serializeFormData() : array;
}