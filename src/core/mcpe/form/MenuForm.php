<?php

namespace core\mcpe\form;

use core\mcpe\form\element\Button;

use pocketmine\utils\Utils;

use pocketmine\form\FormValidationException;

use pocketmine\Player;

class MenuForm extends Form {
    /** @var Button[] */
    protected $buttons;

    protected $text = "";
    /** @var \Closure */
    private $onSubmit;
    /** @var \Closure|null */
    private $onClose;

    public function __construct(string $title, string $text, array $buttons = [], ?\Closure $onSubmit = null, ?\Closure $onClose = null) {
        parent::__construct($title);

        $this->text = $text;
        $this->buttons = $buttons;

        if($onSubmit !== null) {
            Utils::validateCallableSignature(function(Player $player, Button $selected) : void{}, $onSubmit);

            $this->onSubmit = $onSubmit;
        }
        if($onClose !== null) {
            Utils::validateCallableSignature(function(Player $player) : void{}, $onClose);

            $this->onClose = $onClose;
        }
    }

    final public function getType() : string {
        return self::TYPE_MENU;
    }

    protected function serializeFormData() : array {
        return [
            "buttons" => $this->buttons,
            "content" => $this->text
        ];
    }

    final public function handleResponse(Player $player, $data) : void {
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
