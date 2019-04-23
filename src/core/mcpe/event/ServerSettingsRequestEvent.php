<?php

declare(strict_types = 1);

namespace core\mcpe\event;

use core\CorePlayer;

use core\mcpe\form\ServerSettingsForm;

use pocketmine\event\player\PlayerEvent;

class ServerSettingsRequestEvent extends PlayerEvent {
    /** @var ServerSettingsForm|null */
    private $form;

    public function __construct(CorePlayer $player) {
        $this->player = $player;
    }

    public function getForm() : ?ServerSettingsForm {
        return $this->form;
    }

    public function setForm(?ServerSettingsForm $form) : void {
        $this->form = $form;
    }
}