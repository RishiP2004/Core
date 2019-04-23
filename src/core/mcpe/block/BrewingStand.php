<?php

declare(strict_types = 1);

namespace core\mcpe\block;

use core\mcpe\tile\BrewingStand as Tile;

use pocketmine\Player;

use pocketmine\item\Item;

use pocketmine\block\Block;

use pocketmine\math\Vector3;

use pocketmine\nbt\tag\{
    CompoundTag,
    StringTag,
    IntTag
};

class BrewingStand extends \pocketmine\block\BrewingStand {
    public function getBlastResistance() : float {
        return 2.5;
    }

    public function getLightLevel() : int {
        return 1;
    }

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
		$parent = parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);

		if(!$blockReplace->getSide(Vector3::SIDE_DOWN)->isTransparent()) {
			$nbt = new CompoundTag("", [
				new StringTag(Tile::TAG_ID, Tile::BREWING_STAND),
				new IntTag(Tile::TAG_X, $this->x),
				new IntTag(Tile::TAG_Y, $this->y),
				new IntTag(Tile::TAG_Z, $this->z),
			]);
			$nbt->setInt(Tile::TAG_BREW_TIME, Tile::MAX_BREW_TIME);

			if($item->hasCustomName()){
				$nbt->setString("CustomName", $item->getCustomName());
			}
			new Tile($player->getLevel(), $nbt);
		}
		return $parent;
	}

    public function onActivate(Item $item, Player $player = null) : bool {
        $parent = parent::onActivate($item, $player);
        $tile = $player->getLevel()->getTile($this);

        if($tile instanceof Tile) {
            $player->addWindow($tile->getInventory());
        } else {
            $nbt = new CompoundTag("", [
                new StringTag(Tile::TAG_ID, Tile::BREWING_STAND),
                new IntTag(Tile::TAG_X, $this->x),
                new IntTag(Tile::TAG_Y, $this->y),
                new IntTag(Tile::TAG_Z, $this->z),
            ]);
            $nbt->setInt(Tile::TAG_BREW_TIME, Tile::MAX_BREW_TIME);

            if($item->hasCustomName()){
                $nbt->setString("CustomName", $item->getCustomName());
            }
            $tile = new Tile($player->getLevel(), $nbt);
            $player->addWindow($tile->getInventory());
        }
        return $parent;
    }
}