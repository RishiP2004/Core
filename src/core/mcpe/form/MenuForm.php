<?php

namespace core\mcpe\form;

use core\utils\MathUtils;

use pocketmine\Player;

abstract class MenuForm extends BaseForm {
    protected $content;

    private $options;

    public function __construct(string $title, string $text, array $options) {
        assert(MathUtils::validateObjectArray($options, MenuOption::class));
		
        parent::__construct($title);
		
        $this->content = $text;
        $this->options = array_values($options);
    }

    public function getOption(int $position) : ?MenuOption {
        return $this->options[$position] ?? null;
    }

    public function onSubmit(Player $player, int $selectedOption) : void {
    }

    public function onClose(Player $player) : void {
    }

    final public function handleResponse(Player $player, $data) : void {
        if($data === null) {
            $this->onClose($player);
        } else if(is_int($data)) {
            if(!isset($this->options[$data])) {
                throw new FormValidationException("Option $data does not exist");
            }
            $this->onSubmit($player, $data);
        } else {
			throw new FormValidationException("Expected int or null, got " . gettype($data));
		}
	}

    protected function getType() : string {
        return "form";
    }

    protected function serializeFormData() : array {
        return [
            "content" => $this->content,
            "buttons" => $this->options
        ];
    }
}
