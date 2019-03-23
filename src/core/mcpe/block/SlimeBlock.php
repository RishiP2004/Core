<?php

namespace core\mcpe\block;

use pocketmine\block\{
	Solid,
    Block
};

use pocketmine\item\Item;

class SlimeBlock extends Solid {
	protected $id = Block::SLIME_BLOCK;

	public function __construct($meta = 0) {
		$this->meta = $meta;
	}

	public function getName() : string {
		return "Slime Block";
	}

	public function getHardness() : float {
		return 0;
	}

	public function hasEntityCollision() : bool {
		return true;
	}

	public function getDrops(Item $item) : array {
		return [
			Item::get(Item::SLIME_BLOCK, 0, 1),
		];
	}
}