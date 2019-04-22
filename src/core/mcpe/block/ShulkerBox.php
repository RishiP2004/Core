<?php

namespace core\mcpe\block;

use core\mcpe\tile\ShulkerBox as Tile;

use pocketmine\block\{
    Transparent,
    BlockToolType,
    Block
};

use pocketmine\item\{
	Item,
	ItemFactory
};

use pocketmine\math\Vector3;

use pocketmine\Player;

use pocketmine\tile\Container;

class ShulkerBox extends Transparent {
	protected $id = self::SHULKER_BOX;

	public function __construct(int $meta = 0) {
		$this->meta = $meta;
	}

    public function getName() : string {
        return "Shulker Box";
    }

	public function getResistance() : float {
		return 30;
	}

	public function getHardness() : float {
		return 2;
	}

	public function getToolType() : int {
		return BlockToolType::TYPE_PICKAXE;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
		//TODO: Rotation
		$this->getLevel()->setBlock($blockReplace, $this, true, true);

		$nbt = Tile::createNBT($this, $face, $item, $player);
		$items = $item->getNamedTag()->getTag(Container::TAG_ITEMS);

		if($items !== null) {
			$nbt->setTag($items);
		}
		Tile::createTile(Tile::SHULKER_BOX, $this->getLevel(), $nbt);

		($inv = $player->getInventory())->clear($inv->getHeldItemIndex());
		return true;
	}

	public function onActivate(Item $item, Player $player = null) : bool {
		$tile = $this->getLevel()->getTile($this);

		if(!$tile instanceof Tile) {
			$tile = Tile::createTile(Tile::SHULKER_BOX, $this->getLevel(), Tile::createNBT($this));
		}

		if(!$this->getSide(Vector3::SIDE_UP)->isTransparent() or !$tile->canOpenWith($item->getCustomName())) {
			return true;
		}
		$player->addWindow($tile->getInventory());
		return true;
	}

	public function onBreak(Item $item, Player $player = null) : bool {
		/** @var Tile $tile */
		$tile = $this->getLevel()->getTile($this);

		if($tile instanceof Tile) {
			$item = ItemFactory::get($this->id, $this->id !== self::UNDYED_SHULKER_BOX ? $this->meta : 0, 1);
			$itemNBT = clone $item->getNamedTag();

			$itemNBT->setTag($tile->getNBT()->getTag(Container::TAG_ITEMS));
			$item->setNamedTag($itemNBT);
			$this->getLevel()->dropItem($this->asVector3(), $item);
			$tile->getInventory()->clearAll();
		}
		$this->getLevel()->setBlock($this, Block::get(Block::AIR), true, true);
		return true;
	}

	public function getDrops(Item $item) : array {
		return [];
	}
}