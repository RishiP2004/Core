<?php

namespace core\mcpe\item;

use pocketmine\item\Item;

class EyeOfEnder extends Item {
	public function __construct($meta = 0) {
		parent::__construct(self::ENDER_EYE, $meta, "Eye Of Ender");
	}
}