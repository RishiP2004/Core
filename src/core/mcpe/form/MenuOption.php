<?php

namespace core\mcpe\form;

class MenuOption implements \JsonSerializable {
    private $text;

    private $image;

    public function __construct(string $text, ?FormIcon $image = null) {
        $this->text = $text;
        $this->image = $image;
    }

    public function getText() : string {
        return $this->text;
    }

    public function hasImage(): bool {
        return $this->image !== null;
    }

    public function getImage() : ?FormIcon {
        return $this->image;
    }

    public function jsonSerialize() {
        $json = [
            "text" => $this->text
        ];
        if($this->hasImage()) {
            $json["image"] = $this->image;
        }
        return $json;
    }
}
