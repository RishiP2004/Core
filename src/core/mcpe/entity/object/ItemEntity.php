<?php

declare(strict_types = 1);

namespace core\mcpe\entity\object;

use core\mcpe\entity\{
	Collidable,
	CollisionCheckingTrait
};

use pocketmine\item\Item;

use pocketmine\entity\Entity;

use pocketmine\block\Block;

use pocketmine\math\AxisAlignedBB;

class ItemEntity extends \pocketmine\entity\object\ItemEntity implements Collidable {
	use CollisionCheckingTrait;

	public function entityBaseTick(int $tickDiff = 1) : bool {
		foreach($this->level->getNearbyEntities($this->boundingBox->expandedCopy(0.5,0.5,0.5), $this) as $entity) {
			if($this->pickupDelay === 0 and $entity instanceof Item and $entity->onGround and $this->motion->equals($entity->getMotion()) and $this->item->equals($entity->getItem())and ($count = $this->item->getCount() + $entity->getItem()->getCount()) <= $this->item->getMaxStackSize()) {
				$this->item->setCount($count);
				$entity->flagForDespawn();

				foreach($this->getViewers() as $player) {
					$this->sendSpawnPacket($player);
				}
				break;
			}
		}
		return parent::entityBaseTick($tickDiff);
	}

	public function onCollideWithEntity(Entity $entity) : void {
		//TODO: Minecart interactions
	}

	public function onCollideWithBlock(Block $block) : void {
		// TODO: Hoppers, pressure plates, tripwire
	}

	public function push(AxisAlignedBB $source) : void {
	}
}