<?php

declare(strict_types = 1);

namespace core\mcpe\entity\vehicle;

use core\CorePlayer;

use core\mcpe\entity\{
	Interactable,
	Linkable,
	Lookable,
	Collidable,
	LinkableTrait,
	CollisionCheckingTrait,
	CreatureBase
};

use pocketmine\entity\Entity;

use pocketmine\block\Block;

class Boat extends Entity implements Interactable, Linkable, Lookable, Collidable {
	use LinkableTrait, CollisionCheckingTrait;

	public function initEntity() : void {
		parent::initEntity();
	}

	public function getName() : string {
		return "Boat";
	}

	public function entityBaseTick(int $tickDiff = 1) : bool {
		return parent::entityBaseTick($tickDiff);
	}

	public function onPlayerInteract(CorePlayer $player) : void {
	}

	public function onPlayerLook(CorePlayer $player) : void {
		$this->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, "Ride");
	}

	public function onCollideWithEntity(Entity $entity) : void {
		if(!$entity instanceof CorePlayer and $entity instanceof Linkable) {
			$this->setLink($entity);
		}
	}

	public function onCollideWithBlock(Block $block) : void {
		//TODO: Break boat if with speed
		//TODO: Implement onCollideWithBlock() method.
	}

	public function push(CreatureBase $source) : void {
		$base = 0.15;
		$x = ($source->minX + $source->maxX) / 2;
		$z = ($source->minZ + $source->maxZ) / 2;
		$f = sqrt($x * $x + $z * $z);

		if($f <= 0) {
			return;
		}
		$f = 1 / $f;
		$motion = clone $this->motion;
		$motion->x /= 2;
		$motion->z /= 2;
		$motion->x += $x * $f * $base;
		$motion->z += $z * $f * $base;

		$this->setMotion($motion);
	}
}