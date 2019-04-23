<?php

declare(strict_types = 1);

namespace core\mcpe\entity;

use core\CorePlayer;

interface Interactable {
	public function onPlayerInteract(CorePlayer $player) : void;
}