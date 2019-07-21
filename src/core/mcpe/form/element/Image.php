<?php

declare(strict_types = 1);

namespace core\mcpe\form\element;

class Image implements \JsonSerializable {
    public const TYPE_URL = "url";
    public const TYPE_PATH = "path";
	/**
	 * @var string
	 */
    private $data, $type;

    public function __construct(string $data, string $type = self::TYPE_URL) {
        $this->data = $data;
		$this->type = $type;
    }

	public function getData() : string {
		return $this->data;
	}

    public function getType() : string {
        return $this->type;
    }

    public function jsonSerialize() : array {
        return [
			"data" => $this->data,
            "type" => $this->type
        ];
    }
}