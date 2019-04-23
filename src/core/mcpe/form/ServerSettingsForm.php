<?php

declare(strict_types = 1);

namespace core\mcpe\form;

use core\mcpe\form\element\Image;

class ServerSettingsForm extends CustomForm {
    /** @var Image|null */
    protected $image;

    public function __construct(string $title, $elements, ?Image $image, \Closure $onSubmit, ?\Closure $onClose = null) {
        parent::__construct($title, $elements, $onSubmit, $onClose);

        $this->image = $image;
    }

    public function hasImage() : bool {
        return $this->image !== null;
    }

    public function serializeFormData() : array {
        $data = parent::serializeFormData();

        if($this->hasImage()) {
            $data["icon"] = $this->image;
        }
        return $data;
    }
}
