<?php

declare(strict_types = 1);

namespace core\mcpe\entity\animal\walking;

use core\CorePlayer;

use core\mcpe\entity\{
	Collidable,
	Interactable,
	AgeableTrait,
	CollisionCheckingTrait,
	CreatureBase
};

use pocketmine\entity\Entity;

use pocketmine\block\Block;

class Villager extends \pocketmine\entity\Villager implements Collidable, Interactable {
    use AgeableTrait, CollisionCheckingTrait;

	public function onCollideWithEntity(Entity $entity) : void {
    }

    public function onCollideWithBlock(Block $block) : void {
    }

    public function push(CreatureBase $source) : void {
    }

    public function onPlayerInteract(CorePlayer $player) : void {
    }
}