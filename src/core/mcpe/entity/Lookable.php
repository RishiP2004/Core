<?php

declare(strict_types = 1);

namespace core\mcpe\entity;

use core\CorePlayer;

interface Lookable {
	public function onPlayerLook(CorePlayer $player) : void;
}