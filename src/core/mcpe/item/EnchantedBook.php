<?php

declare(strict_types = 1);

namespace core\mcpe\item;

use pocketmine\item\Item;

class EnchantedBook extends Item {
	public function __construct(int $meta = 0) {
		parent::__construct(self::ENCHANTED_BOOK, $meta, "Enchanted Book");
	}

	public function getMaxStackSize() : int {
		return 1;
	}
}