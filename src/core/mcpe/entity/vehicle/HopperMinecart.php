<?php

declare(strict_types = 1);

namespace core\mcpe\entity\vehicle;

class HopperMinecart extends Minecart {
	public function initEntity() : void {
		parent::initEntity();
	}

	public function getName() : string {
		return "Hopper Minecart";
	}

	public function entityBaseTick(int $tickDiff = 1) : bool {
		return parent::entityBaseTick($tickDiff);
	}
}