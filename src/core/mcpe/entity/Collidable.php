<?php

declare(strict_types = 1);

namespace core\mcpe\entity;

use pocketmine\entity\Entity;

use pocketmine\block\Block;

interface Collidable {
	public function onCollideWithEntity(Entity $entity) : void;

	public function onCollideWithBlock(Block $block) : void;

	public function push(CreatureBase $source) : void;
}