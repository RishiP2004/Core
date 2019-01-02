<?php

namespace core\mcpe\block;

use CortexPE\inventory\EnchantInventory;
use CortexPE\Main;
use pocketmine\block\Block;
use pocketmine\block\EnchantingTable as PMEnchantingTable;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\Player;
use pocketmine\tile\EnchantTable;
use pocketmine\tile\Tile;

class EnchantingTable extends PMEnchantingTable {

	public function onActivate(Item $item, Player $player = null): bool{
		if(Main::$EnchantingTableEnabled && !(Main::$limitedCreative && $player->isCreative())){
			if($player instanceof Player){
				$this->getLevel()->setBlock($this, $this, true, true);
				Tile::createTile(Tile::ENCHANT_TABLE, $this->getLevel(), EnchantTable::createNBT($this));
			}

			$player->addWindow(new EnchantInventory($this), WindowTypes::ENCHANTMENT);
		}

		return true;
	}

	public function countBookshelf(): int{
		$count = 0;
		$level = $this->getLevel();

		for($y = 0; $y <= 1; $y++){
			for($x = -1; $x <= 1; $x++){
				for($z = -1; $z <= 1; $z++){
					if($z == 0 && $x == 0) continue;
					if($level->getBlock($this->add($x, 0, $z))->isTransparent()){
						if($level->getBlock($this->add(0, 1, 0))->isTransparent()){
							//diagonal and straight
							if($level->getBlock($this->add($x << 1, $y, $z << 1))->getId() == Block::BOOKSHELF){
								$count++;
							}

							if($x != 0 && $z != 0){
								//one block diagonal and one straight
								if($level->getBlock($this->add($x << 1, $y, $z))->getId() == Block::BOOKSHELF){
									++$count;
								}

								if($level->getBlock($this->add($x, $y, $z << 1))->getId() == Block::BOOKSHELF){
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