<?php

declare(strict_types = 1);

namespace core\mcpe\form;

use core\mcpe\form\element\Element;

use pocketmine\Player;

use pocketmine\utils\Utils;

use pocketmine\form\FormValidationException;

class CustomForm extends Form {
    /** @var Element[] */
    private $elements;
    /** @var \Closure */
    private $onSubmit;
    /** @var \Closure|null */
    private $onClose;

    public function __construct(string $title, array $elements, \Closure $onSubmit, ?\Closure $onClose = null) {
        parent::__construct($title);

        $this->elements = $elements;
        $this->onSubmit = $onSubmit;
        $this->onClose = $onClose;

        Utils::validateCallableSignature(function(Player $player, CustomFormResponse $response) : void{}, $onSubmit);

        $this->onSubmit = $onSubmit;

        if($onClose !== null) {
            Utils::validateCallableSignature(function(Player $player) : void{}, $onClose);
            $this->onClose = $onClose;
        }
    }

    final public function getType() : string {
        return self::TYPE_CUSTOM_FORM;
    }

	public function append(Element ...$elements) : self {
		$this->elements = array_merge($this->elements, $elements);
		return $this;
	}

    protected function serializeFormData() : array {
        return [
            "content" => $this->elements
        ];
    }

	final public function handleResponse(Player $player, $data) : void {
		if($data === null) {
			if($this->onClose !== null) {
				($this->onClose)($player);
			}
		} else if(is_array($data)) {
			foreach($data as $index => $value) {
				if(!isset($this->elements[$index])) {
					throw new FormValidationException("Element at index $index does not exist");
				}
				$element = $this->elements[$index];

				$element->validate($value);
				$element->setValue($value);
			}
			($this->onSubmit)($player, new CustomFormResponse($this->elements));
		} else {
			throw new FormValidationException("Expected array or null, got " . gettype($data));
		}
	}
}
