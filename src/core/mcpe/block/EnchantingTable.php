<?php

declare(strict_types = 1);

namespace core\mcpe\block;

use core\mcpe\inventory\EnchantInventory;

use pocketmine\Player;

use pocketmine\item\Item;

use pocketmine\tile\EnchantTable as Tile;

use pocketmine\network\mcpe\protocol\types\WindowTypes;

use pocketmine\block\Block;

class EnchantingTable extends \pocketmine\block\EnchantingTable {
	public function onActivate(Item $item, Player $player = null) : bool {
        if($player instanceof Player) {
            $this->getLevel()->setBlock($this, $this, true, true);
            Tile::createTile(Tile::ENCHANT_TABLE, $this->getLevel(), Tile::createNBT($this));
        }
        $player->addWindow(new EnchantInventory($this), WindowTypes::ENCHANTMENT);
		return true;
	}

	public function countBookshelf() : int {
		$count = 0;
		$level = $this->getLevel();

		for($y = 0; $y <= 1; $y++) {
			for($x = -1; $x <= 1; $x++) {
				for($z = -1; $z <= 1; $z++) {
					if($z === 0 && $x === 0) {
					    continue;
                    }
					if($level->getBlock($this->add($x, 0, $z))->isTransparent()) {
						if($level->getBlock($this->add(0, 1, 0))->isTransparent()) {
							if($level->getBlock($this->add($x << 1, $y, $z << 1))->getId() == Block::BOOKSHELF) {
								$count++;
							}
							if($x != 0 && $z != 0) {
								if($level->getBlock($this->add($x << 1, $y, $z))->getId() == Block::BOOKSHELF) {
									++$count;
								}
								if($level->getBlock($this->add($x, $y, $z << 1))->getId() == Block::BOOKSHELF) {
									++$count;
								}
							}
						}
					}
				}
			}
		}
		return $count;
	}
}