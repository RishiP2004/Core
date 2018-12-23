<?php

namespace core\mcpe\form\element;

class Label extends CustomFormElement {
    public function getType() : string {
        return "label";
    }

    public function validateValue($value) : void {
        assert($value === null);
    }

    protected function serializeElementData() : array {
        return [];
    }
}
