<?php

declare(strict_types = 1);

namespace core\mcpe\form\element;

class Dropdown extends Element {
    /** @var string[] */
    protected $options;

    protected $default = 0;

    public function __construct(string $text, array $options, int $default = 0) {
        parent::__construct($text);

        $this->options = $options;
        $this->default = $default;
    }

    public function getType() : string {
        return "dropdown";
    }

    public function getOptions() : array {
        return $this->options;
    }

    public function getSelectedOption() : ?string {
        return $this->options[$this->value] ?? null;
    }

    public function getDefault() : int {
        return $this->default;
    }

    public function serializeElementData() : array {
        return [
            "options" => $this->options,
            "default" => $this->default
        ];
    }
}
