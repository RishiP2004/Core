<?php

namespace core\mcpe\item;

use pocketmine\Player;

use pocketmine\block\Block;

use pocketmine\math\Vector3;

use pocketmine\item\{
	Item,
	Potion
};

class GlassBottle extends \pocketmine\item\GlassBottle {
	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool {
		if(in_array($blockClicked->getId(), [
			Block::STILL_WATER,
				Block::FLOWING_WATER
			]) or in_array($blockReplace->getId(), [
				Block::STILL_WATER,
				Block::FLOWING_WATER])) {
			if($player->isSurvival()) {
				$this->count--;
			}
			$player->getInventory()->addItem(Item::get(Item::POTION, Potion::WATER, 1));
		}
		return true;
	}
}