<?php

declare(strict_types = 1);

namespace core\mcpe\form;

use core\mcpe\form\element\Button;

use pocketmine\Player;

use pocketmine\utils\Utils;

use pocketmine\form\FormValidationException;

class MenuForm extends Form {
    /** @var Button[] */
	protected $buttons = [];
	/** @var string */
	protected $text;
	/** @var Closure|null */
	private $onSubmit, $onClose;

	public function __construct(string $title, string $text = "", array $buttons = [], ?\Closure $onSubmit = null, ?\Closure $onClose = null) {
		parent::__construct($title);

		$this->text = $text;
		
		$this->append(...$buttons);
		$this->setOnSubmit($onSubmit);
		$this->setOnClose($onClose);
	}
	
	final public function getType() : string {
		return self::TYPE_MENU;
	}

	public function setText(string $text) : self {
		$this->text = $text;
		return $this;
	}

	public function append(...$buttons) : self {
		if(isset($buttons[0]) && is_string($buttons[0])) {
			$buttons = Button::createFromList(...$buttons);
		}
		$this->buttons = array_merge($this->buttons, $buttons);
		return $this;
	}

	public function setOnSubmit(?\Closure $onSubmit) : self {
		if($onSubmit !== null) {
			Utils::validateCallableSignature(function(Player $player, Button $selected) : void{}, $onSubmit);

			$this->onSubmit = $onSubmit;
		}
		return $this;
	}

	public function setOnClose(?\Closure $onClose) : self {
		if($onClose !== null) {

			Utils::validateCallableSignature(function(Player $player) : void{}, $onClose);

			$this->onClose = $onClose;
		}
		return $this;
	}

	protected function serializeFormData() : array {
		return [
			"buttons" => $this->buttons,
			"content" => $this->text
		];
	}

	final public function handleResponse(Player $player, $data) : void{
		if($data === null) {
			if($this->onClose !== null) {
				($this->onClose)($player, $data);
			}
		} else if(is_int($data)) {
			if(!isset($this->buttons[$data])) {
				throw new FormValidationException("Button with index $data does not exist");
			}
			if($this->onSubmit !== null) {
				$button = $this->buttons[$data];

				$button->setValue($data);
				($this->onSubmit)($player, $button);
			}
		} else {
			throw new FormValidationException("Expected int or null, got " . gettype($data));
		}
	}
}
