<?php

namespace core\mcpe\block;

use core\mcpe\tile\ShulkerBox as Tile;

use pocketmine\block\{
    Transparent,
    BlockToolType,
    Block
};

use pocketmine\item\Item;

use pocketmine\math\Vector3;

use pocketmine\Player;

use pocketmine\nbt\tag\{
    CompoundTag,
    ListTag,
    StringTag
};

use pocketmine\tile\Container;

class ShulkerBox extends Transparent {
	protected $id = self::SHULKER_BOX;

	public function __construct(int $meta = 0) {
        parent::__construct($this->id, $meta);

	    $this->meta = $meta;
	}

    public function getName() : string {
        return "Shulker Box";
    }

	public function getResistance() : float {
		return 30;
	}

	public function getHardness() : float {
		return 6;
	}

	public function getToolType() : int {
		return BlockToolType::TYPE_PICKAXE;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
		$this->getLevel()->setBlock($blockReplace, $this, true, true);
		/** @var CompoundTag $nbt */
		$nbt = Tile::createNBT($this->asVector3());

		if($item->getNamedTag()->hasTag("Items", ListTag::class)) {
			$nbt->setTag($item->getNamedTag()->getListTag("Items"));
		} else {
			$nbt->setTag(new ListTag("Items", []));
		}
		if($item->hasCustomName()) {
			$nbt->setString("CustomName", $item->getCustomName());
		}
		/** @var Tile $tile */
		Tile::createTile(Tile::SHULKER_BOX, $this->getLevel(), Tile::createNBT($this, $face, $item, $player));
		$player->getInventory()->setItemInHand(Item::get(Item::AIR));
		return true;
	}

	public function onBreak(Item $item, Player $player = null): bool{
		/** @var Tile $tile */
		$tile = $this->getLevel()->getTile($this);

		if($tile instanceof Tile) {
			$item = Item::get(Item::SHULKER_BOX, $this->meta, 1);
			$itemNBT = clone $item->getNamedTag();

			$itemNBT->setTag($tile->getNBT()->getTag(Container::TAG_ITEMS));
			$item->setNamedTag($itemNBT);
			$this->getLevel()->dropItem($this->asVector3(), $item);
			$tile->getInventory()->clearAll(); // dont drop the items
		}
		$this->getLevel()->setBlock($this, Block::get(Block::AIR), true, true);
		return true;
	}

	public function onActivate(Item $item, Player $player = null) : bool {
	    if($player instanceof Player) {
            $tile = $this->getLevel()->getTile($this);
            $shulkerBox = null;

            if($tile instanceof Tile) {
                $shulkerBox = $tile;
            } else {
                $nbt = Tile::createNBT($this->asVector3());

                $nbt->setTag(new ListTag("Items", []));

                $shulkerBox = Tile::createTile(Tile::SHULKER_BOX, $this->getLevel(), $nbt);
            }
            if(!$this->getSide(Vector3::SIDE_UP)->isTransparent() or ($shulkerBox->getNBT()->hasTag("Lock", StringTag::class) and $shulkerBox->getNBT()->getString("Lock") !== $item->getCustomName())) {
                return true;
            }
            $player->addWindow($shulkerBox->getInventory());
        }
		return true;
	}

	public function getDrops(Item $item) : array {
		return [];
	}
}