<?php

declare(strict_types = 1);

namespace core\mcpe\form;

use pocketmine\utils\Utils;

use pocketmine\Player;

use pocketmine\form\FormValidationException;

class ModalForm extends Form {

    protected $text = "";

    private $yesButton = "", $noButton = "";
    /** @var \Closure */
    private $onSubmit;

    public function __construct(string $title, string $text, \Closure $onSubmit, $yesButton = "gui.yes", string $noButton = "gui.no") {
        parent::__construct($title);

        $this->text = $text;
        $this->yesButton = $yesButton;
        $this->noButton = $noButton;

        Utils::validateCallableSignature(function(Player $player, bool $response) : void{}, $onSubmit);

        $this->onSubmit = $onSubmit;
    }

    final public function getType() : string {
        return self::TYPE_MODAL;
    }

    public function getYesButtonText() : string {
        return $this->yesButton;
    }

    public function getNoButtonText() : string {
        return $this->noButton;
    }

    protected function serializeFormData() : array {
        return [
            "content" => $this->text,
            "button1" => $this->yesButton,
            "button2" => $this->noButton
        ];
    }

	public function onSubmit(Player $player, bool $choice) : void {
	}

    final public function handleResponse(Player $player, $data) : void {
        if(!is_bool($data)) {
            throw new FormValidationException("Expected bool, got " . gettype($data));
        }
        $this->onSubmit($player, $data);
    }
}
