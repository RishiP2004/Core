<?php

namespace core\mcpe\form;

class FormIcon implements \JsonSerializable {
    public const IMAGE_TYPE_URL = "url";
    public const IMAGE_TYPE_PATH = "path";

    private $type;

    private $data;

    public function __construct(string $data, string $type = self::IMAGE_TYPE_URL) {
        $this->type = $type;
        $this->data = $data;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getData(): string {
        return $this->data;
    }

    public function jsonSerialize() {
        return [
            "type" => $this->type,
            "data" => $this->data
        ];
    }
}
