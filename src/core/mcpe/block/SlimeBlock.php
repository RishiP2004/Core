<?php

namespace core\mcpe\block;

use pocketmine\block\{
	Block, Solid
};
use pocketmine\item\Item;

class SlimeBlock extends Solid {

	/**
	 * @var int $id
	 */
	protected $id = Block::SLIME_BLOCK;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	/**
	 * @return string
	 */
	public function getName(): string{
		return "Slime Block";
	}

	/**
	 * @return float
	 */
	public function getHardness(): float{
		return 0;
	}

	/**
	 * @return bool
	 */
	public function hasEntityCollision(): bool{
		return true;
	}

	/**
	 * @param Item $item
	 * @return array
	 */
	public function getDrops(Item $item): array{
		return [
			Item::get(Item::SLIME_BLOCK, 0, 1),
		];
	}
}