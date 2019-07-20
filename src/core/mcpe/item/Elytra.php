<?php

declare(strict_types = 1);

namespace core\mcpe\item;

use pocketmine\item\{
	Durable, 
	Item
};

class Elytra extends Durable {
	public function __construct($meta = 0) {
		parent::__construct(Item::ELYTRA, $meta, "Elytra Wings");
	}

	public function getMaxDurability() : int {
		return 433;
	}
}