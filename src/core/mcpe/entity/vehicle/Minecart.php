<?php

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

use pocketmine\math\AxisAlignedBB;

class Minecart extends Entity implements Interactable, Linkable, Lookable, Collidable {
	use LinkableTrait, CollisionCheckingTrait;
	/** @var CreatureBase $link */
	private $link;

	public function initEntity() : void {
		parent::initEntity();
	}

	public function getName() : string {
		return "Minecart";
	}

	public function entityBaseTick(int $tickDiff = 1) : bool {
		return parent::entityBaseTick($tickDiff);
	}

	public function onPlayerInteract(CorePlayer $player) : void {
		// TODO: Implement onPlayerInteract() method.
	}

	public function onPlayerLook(CorePlayer $player) : void {
		$this->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, "Ride");
	}

	public function onCollideWithEntity(Entity $entity) : void {
		// TODO: Implement onCollideWithEntity() method.
	}

	public function onCollideWithBlock(Block $block) : void {
		// TODO: Implement onCollideWithBlock() method.
	}

	public function push(AxisAlignedBB $source) : void {
		// TODO: Implement push() method.
	}
}