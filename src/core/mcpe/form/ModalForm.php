<?php

namespace core\mcpe\form;

use pocketmine\Player;

abstract class ModalForm extends BaseForm {
    private $content;

    private $button1, $button2;

    public function __construct(string $title, string $text, string $yesButtonText = "gui.yes", string $noButtonText = "gui.no") {
        parent::__construct($title);
		
        $this->content = $text;
        $this->button1 = $yesButtonText;
        $this->button2 = $noButtonText;
    }

    public function getYesButtonText() : string {
        return $this->button1;
    }

    public function getNoButtonText() : string {
        return $this->button2;
    }

    public function onSubmit(Player $player, bool $choice) : void {
    }

    final public function handleResponse(Player $player, $data) : void {
        if(!is_bool($data)) {
            throw new FormValidationException("Expected bool, got " . gettype($data));
        }
        $this->onSubmit($player, $data);
    }

    protected function getType() : string {
        return "modal";
    }

    protected function serializeFormData() : array {
        return [
            "content" => $this->content,
            "button1" => $this->button1,
            "button2" => $this->button2
        ];
    }
}
