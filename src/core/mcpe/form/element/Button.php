<?php

namespace core\mcpe\form\element;

class Button extends Element {
    /** @var Image|null */
    protected $image;

    protected $type = "";

    public function __construct(string $text, ?Image $image = null) {
        parent::__construct($text);

        $this->image = $image;
    }

    public function getType() : ?string {
        return null;
    }

    public function hasImage() : bool {
        return $this->image !== null;
    }

    public function serializeElementData() : array {
        $data = [
            "text" => $this->text
        ];

        if($this->hasImage()) {
            $data["image"] = $this->image;
        }
        return $data;
    }
}
