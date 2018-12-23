<?php

namespace core\mcpe\entity;

use pocketmine\entity\Entity;

use pocketmine\block\Block;

use pocketmine\math\AxisAlignedBB;

interface Collidable {
	public function onCollideWithEntity(Entity $entity) : void;

	public function onCollideWithBlock(Block $block) : void;

	public function push(AxisAlignedBB $source) : void;
}