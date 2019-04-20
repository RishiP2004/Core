<?php

namespace core\mcpe\item;

use pocketmine\Player;

use pocketmine\block\Block;

use pocketmine\math\Vector3;

use pocketmine\entity\Entity;

class Minecart extends \pocketmine\item\Minecart {
	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool {
		$level = $player->getLevel();
		$entity = Entity::createEntity(Entity::MINECART, $level, Entity::createBaseNBT($blockReplace->add(0.5, 0, 0.5)));

		$entity->spawnToAll();

		if($player->isSurvival()) {
			$this->count--;
		}
		return true;
	}
}