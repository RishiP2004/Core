<?php

declare(strict_types = 1);

namespace core\mcpe\form;

abstract class Form implements \pocketmine\form\Form {
    protected const TYPE_MODAL = "modal";
    protected const TYPE_MENU = "form";
    protected const TYPE_CUSTOM_FORM = "custom_form";

    protected $title = "";

    public function __construct(string $title) {
        $this->title = $title;
    }

    final public function jsonSerialize() : array {
        return array_merge([
            "title" => $this->getTitle(),
            "type" => $this->getType()
        ], $this->serializeFormData());
    }

    public function getTitle() : string {
        return $this->title;
    }

    abstract public function getType() : string;

    abstract protected function serializeFormData() : array;
}