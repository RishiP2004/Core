<?php

declare(strict_types = 1);

namespace core\mcpe\form;

abstract class Form implements \pocketmine\form\Form {
    protected const TYPE_MODAL = "modal";
    protected const TYPE_MENU = "form";
    protected const TYPE_CUSTOM_FORM = "custom_form";
	/** @var string */
	private $title;
	/** @var \Closure|null */
	private $onCreate, $onDestroy;

    public function __construct(string $title) {
        $this->title = $title;
    }

	abstract public function getType() : string;

	public function getTitle() : string {
		return $this->title;
	}

	public function setTitle(string $title) : self {
		$this->title = $title;
		return $this;
	}

	public function setOnCreate(\Closure $onCreate) : self {
		$this->onCreate = $onCreate;
		return $this;
	}

    final public function jsonSerialize() : array {
		if($this->onCreate !== null) {
			($this->onCreate)();
		}
		return array_merge([
			"title" => $this->getTitle(),
			"type" => $this->getType()
		], $this->serializeFormData());
    }

	public function setOnDestroy(\Closure $onDestroy) : self {
		$this->onDestroy = $onDestroy;
		return $this;
	}

	public function __destruct() {
		if($this->onDestroy !== null) {
			($this->onDestroy)();
		}
	}

    abstract protected function serializeFormData() : array;
}