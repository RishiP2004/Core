<?php

declare(strict_types = 1);

namespace core\mcpe\entity\vehicle;

class ChestMinecart extends Minecart {
	public function initEntity() : void {
		parent::initEntity();
	}

	public function getName() : string {
		return "Chest Minecart";
	}

	public function entityBaseTick(int $tickDiff = 1) : bool {
		return parent::entityBaseTick($tickDiff);
	}
}