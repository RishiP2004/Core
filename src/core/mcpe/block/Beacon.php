<?php

namespace core\mcpe\block;

use core\CorePlayer;

use core\mcpe\inventory\BeaconInventory;

use core\mcpe\tile\Beacon as Tile;

use pocketmine\item\Item;

use pocketmine\block\{
	Air, 
	Block, 
	Transparent
};

use pocketmine\math\Vector3;

use pocketmine\Player;

use pocketmine\nbt\tag\{
	ByteTag, 
	CompoundTag, 
	IntTag, 
	StringTag
};

class Beacon extends Transparent {
	protected $id = self::BEACON;

	public function __construct($meta = 0) {
		$this->meta = $meta;
	}

	public function getName() : string {
		return "Beacon";
	}

	public function getLightLevel() : int {
		return 15;
	}

	public function getBlastResistance() : float {
		return 15;
	}

	public function getHardness() : float {
		return 3;
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
		$this->getLevel()->setBlock($this, $this, true);

		$nbt = new CompoundTag("", [
			new StringTag("id", Tile::BEACON),
			new ByteTag("isMovable", 0),
			new IntTag("primary", 0),
			new IntTag("secondary", 0),
			new IntTag("x", $blockReplace->x),
			new IntTag("y", $blockReplace->y),
			new IntTag("z", $blockReplace->z),
		]);
		Tile::createTile(Tile::BEACON, $this->getLevel(), $nbt);
		return true;
	}

	public function canBeActivated() : bool {
		return true;
	}

	public function onActivate(Item $item, Player $player = null) : bool {
		if(!$player instanceof CorePlayer) {
			return false;
		}
		$tile = $this->getLevel()->getTile($this);
		/** @var BeaconInventory $beacon */
		$beacon = null;
		
		if($tile instanceof Tile) {
			/** @var Tile $beacon */
			$beacon = $tile;
		} else {
			$nbt = new CompoundTag("", [
				new StringTag("id", Tile::BEACON),
				new ByteTag("isMovable", 0),
				new IntTag("primary", 0),
				new IntTag("secondary", 0),
				new IntTag("x", $this->x),
				new IntTag("y", $this->y),
				new IntTag("z", $this->z),
			]);
			$beacon = Tile::createTile(Tile::BEACON, $this->getLevel(), $nbt);
		}
		$inventory = $beacon->getInventory();
		
		if($inventory instanceof BeaconInventory) {
			$player->addWindow($beacon->getInventory());
		}
		return true;
	}

	public function onBreak(Item $item, Player $player = null) : bool {
		$this->getLevel()->setBlock($this, new Air(), true);
		return true;
	}
}