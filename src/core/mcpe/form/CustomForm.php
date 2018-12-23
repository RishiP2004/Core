<?php

namespace core\mcpe\form;

use core\mcpe\form\element\CustomFormElement;

use core\utils\MathUtils;

use pocketmine\Player;

abstract class CustomForm extends BaseForm {
    private $elements;

    private $elementMap = [];

    public function __construct(string $title, array $elements) {
        assert(MathUtils::validateObjectArray($elements, CustomFormElement::class));
		
        parent::__construct($title);
		
        $this->elements = array_values($elements);
		
        foreach($this->elements as $element) {
            if(isset($this->elements[$element->getName()])) {
                throw new \InvalidArgumentException("Multiple elements cannot have the same name, found \"" . $element->getName() . "\" more than once");
            }
            $this->elementMap[$element->getName()] = $element;
        }
    }

    public function getElement(int $index) : ?CustomFormElement {
        return $this->elements[$index] ?? null;
    }
	
    public function getElementByName(string $name) : ?CustomFormElement {
        return $this->elementMap[$name] ?? null;
    }

    public function getAllElements() : array {
        return $this->elements;
    }

    public function onSubmit(Player $player, CustomFormResponse $data) : void {
    }

    public function onClose(Player $player) : void {
    }

    public function handleResponse(Player $player, $data) : void {
        if($data === null) {
            $this->onClose($player);
        } else if(is_array($data)) {
            if(($actual = count($data)) !== ($expected = count($this->elements))) {
                throw new FormValidationException("Expected $expected result data, got $actual");
            }
            $values = [];
            /** @var array $data */
            foreach($data as $index => $value) {
                if(!isset($this->elements[$index])) {
                    throw new FormValidationException("element at offset $index does not exist");
                }
                $element = $this->elements[$index];
				
                try {
                    $element->validateValue($value);
                } catch(FormValidationException $e) {
                    throw new FormValidationException("Validation failed for element \"" . $element->getName() . "\": " . $e->getMessage(), 0, $e);
                }
                $values[$element->getName()] = $value;
            }
            $this->onSubmit($player, new CustomFormResponse($values));
        } else {
			throw new FormValidationException("Expected array or null, got " . gettype($data));
		}
    }

    protected function getType() : string {
        return "custom_form";
    }

    protected function serializeFormData() : array {
        return [
            "content" => $this->elements
        ];
    }
}
