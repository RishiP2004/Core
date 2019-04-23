<?php

declare(strict_types = 1);

namespace core\mcpe\entity;

use pocketmine\block\{
    Block,
    Ladder,
    Vine
};

trait ClimbingTrait {
	public function initEntity() : void {
		$this->setCanClimb();

		parent::initEntity();
	}

	public function onCollideWithBlock(Block $block) : void {
		if($this->canClimbWalls()) {
			$this->motion->y += 0.5;
		}
		if($this->canClimb() and ($block instanceof Ladder or $block instanceof Vine)) {
			$this->motion->y += 0.5;
		}
		parent::onCollideWithBlock($block);
	}
}