<?php

namespace core\mcpe\entity\vehicle;

class CommandBlockMinecart extends Minecart {
	public function initEntity() : void {
		parent::initEntity();
	}

	public function getName() : string {
		return "Command Block Minecart";
	}

	public function entityBaseTick(int $tickDiff = 1) : bool {
		return parent::entityBaseTick($tickDiff);
	}
}