<?php

namespace core\mcpe\block;

use core\mcpe\tile\Hopper as Tile;

use pocketmine\block\{
    Transparent,
    BlockToolType,
    Block
};

use pocketmine\item\Item;

use pocketmine\Player;

use pocketmine\nbt\tag\{
    CompoundTag,
    ListTag,
    StringTag,
    IntTag
};

use pocketmine\math\Vector3;

class Hopper extends Transparent {
	protected $id = self::HOPPER_BLOCK;

	public function __construct(int $meta = 0) {
        parent::__construct($meta);
	}

    public function getName() : string {
        return "Hopper";
    }

    public function canBeActivated() : bool {
		return true;
	}

	public function getToolType() : int {
		return BlockToolType::TYPE_PICKAXE;
	}

	public function getHardness() : float {
		return 3;
	}

	public function getBlastResistance() : float {
		return 24;
	}

	public function onActivate(Item $item, Player $player = null) : bool {
        if($player instanceof Player) {
            $tile = $this->getLevel()->getTile($this);

            if($tile instanceof Tile) {
                $player->addWindow($tile->getInventory());
            } else {
                $nbt = new CompoundTag("", [
                    new ListTag("Items", []),
                    new StringTag("id", Tile::HOPPER),
                    new IntTag("x", $this->x),
                    new IntTag("y", $this->y),
                    new IntTag("z", $this->z),
                ]);
                $t = Tile::createTile(Tile::HOPPER, $this->getLevel(), $nbt);

                $player->addWindow($t->getInventory());
            }
        }
        return true;
    }

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
		$faces = [
			0 => 0,
			1 => 0,
			2 => 3,
			3 => 2,
			4 => 5,
			5 => 4,
		];
		$this->meta = $faces[$face];

		$this->getLevel()->setBlock($blockReplace, $this, true, true);

		$nbt = new CompoundTag("", [
			new ListTag("Items", []),
			new StringTag("id", Tile::HOPPER),
			new IntTag("x", $this->x),
			new IntTag("y", $this->y),
			new IntTag("z", $this->z),
		]);

		if($item->hasCustomName()){
			$nbt->setString("CustomName", $item->getCustomName());
		}

		if($item->hasCustomBlockData()){
			foreach($item->getCustomBlockData() as $key => $v){
				$nbt->{$key} = $v;
			}
		}
		Tile::createTile(Tile::HOPPER, $this->getLevel(), $nbt);
		return true;
	}

	public function getDrops(Item $item) : array {
		return [
		    Item::get(Item::HOPPER, 0, 1)
        ];
	}
}
