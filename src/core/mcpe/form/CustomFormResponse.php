<?php

namespace core\mcpe\form;

use core\mcpe\form\{
    Element,
    Label,
    Dropdown,
    Input,
    Slider,
    StepSlider,
    Toggle
};

use pocketmine\form\FormValidationException;

class CustomFormResponse {
    private $elements;

    public function __construct(array $elements) {
        $this->elements = $elements;
    }

    public function tryGet(string $expected = Element::class) {
        if(($element = array_shift($this->elements)) instanceof Label) {
            return $this->tryGet($expected);
        } else if($element === null or !$element instanceof $expected) {
            throw new FormValidationException("Expected a element with of type $expected, got " . get_class($element));
        }
        return $element;
    }

    public function getDropdown() : Dropdown {
        return $this->tryGet(Dropdown::class);
    }

    public function getInput() : Input {
        return $this->tryGet(Input::class);
    }

    public function getSlider() : Slider {
        return $this->tryGet(Slider::class);
    }

    public function getStepSlider() : StepSlider {
        return $this->tryGet(StepSlider::class);
    }

    public function getToggle() : Toggle {
        return $this->tryGet(Toggle::class);
    }
}
