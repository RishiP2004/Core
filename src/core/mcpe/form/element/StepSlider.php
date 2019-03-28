<?php

namespace core\mcpe\form\element;

class StepSlider extends Dropdown {

    public function getType() : string {
        return "step_slider";
    }

    public function serializeElementData() : array {
        return [
            "steps" => $this->options,
            "default" => $this->default
        ];
    }
}