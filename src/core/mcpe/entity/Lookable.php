<?php

namespace core\mcpe\entity;

use core\CorePlayer;

interface Lookable {
	public function onPlayerLook(CorePlayer $player) : void;
}