<?php

namespace core\mcpe\item;

use pocketmine\item\Item;

use pocketmine\Player;

use pocketmine\block\Block;

use pocketmine\math\Vector3;

use pocketmine\entity\Entity;

class ArmorStand extends Item {
	public function __construct(int $meta = 0) {
		parent::__construct(self::ARMOR_STAND, $meta, "Armor Stand");
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool {
		$entity = Entity::createEntity(Entity::ARMOR_STAND, $player->getLevel(), Entity::createBaseNBT($blockReplace->add(0.5, 0, 0.5), null, $this->getDirection($player->getYaw())));

		if($entity instanceof \core\mcpe\entity\object\ArmorStand) {
			if($player->isSurvival()) {
				$this->count--;
			}
			$entity->spawnToAll();
		}
		return true;
	}

	public function getDirection($yaw) : float {
		$rotation = $yaw % 360;

		if($rotation < 0) {
			$rotation += 360;
		}
		if((0 <= $rotation && $rotation < 22.5) || (337.5 <= $rotation && $rotation < 360)) {
			return 180;
		} else if(22.5 <= $rotation && $rotation < 67.5) {
			return 225;
		} else if(67.5 <= $rotation && $rotation < 112.5) {
			return 270;
		} else if(112.5 <= $rotation && $rotation < 157.5) {
			return 315;
		} else if(157.5 <= $rotation && $rotation < 202.5) {
			return 0;
		} else if(202.5 <= $rotation && $rotation < 247.5) {
			return 45;
		} else if(247.5 <= $rotation && $rotation < 292.5) {
			return 90;
		} else if(292.5 <= $rotation && $rotation < 337.5) {
			return 135;
		} else {
			return 0;
		}
	}
}