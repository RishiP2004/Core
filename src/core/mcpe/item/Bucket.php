<?php

namespace core\mcpe\item;

use core\utils\Level;

use pocketmine\Player;

use pocketmine\block\Block;

use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\types\DimensionIds;

class Bucket extends \pocketmine\item\Bucket {
	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool {
		if(Level::getDimension($player->getLevel()) == DimensionIds::NETHER && $this->getOutputBlockID() === Block::WATER) {
			return false;
		}
		return parent::onActivate($player, $blockReplace, $blockClicked, $face, $clickVector);
	}

	public function getOutputBlockID() : int {
		return $this->meta + 1;
	}
}