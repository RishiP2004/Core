<?php

namespace core\mcpe\entity;

use core\CorePlayer;

interface Interactable {
	public function onPlayerInteract(CorePlayer $player) : void;
}