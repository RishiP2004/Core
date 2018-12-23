<?php

namespace core\mcpe\form;

abstract class ServerSettingsForm extends CustomForm {
    private $icon;

    public function __construct(string $title, array $elements, ?FormIcon $icon = null) {
        parent::__construct($title, $elements);
		
        $this->icon = $icon;
    }

    public function hasIcon() : bool {
        return $this->icon !== null;
    }

    public function getIcon() : ?FormIcon {
        return $this->icon;
    }

    protected function serializeFormData() : array {
        $data = parent::serializeFormData();
		
        if($this->hasIcon()) {
            $data["icon"] = $this->icon;
        }
        return $data;
    }
}
