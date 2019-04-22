<?php

namespace core\mcpe\entity\projectile;

use pocketmine\entity\projectile\Projectile;

class WitherSkull extends Projectile {
	public function initEntity() : void {
		parent::initEntity();
	}

	public function getName() : string {
		return "Wither Skull";
	}

	public function entityBaseTick(int $tickDiff = 1) : bool {
		return parent::entityBaseTick($tickDiff);
	}
}