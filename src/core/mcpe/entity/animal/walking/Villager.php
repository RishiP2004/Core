<?php

namespace core\mcpe\entity\animal\walking;

use core\CorePlayer;

use core\mcpe\entity\{
    Collidable,
    Interactable,
    AgeableTrait,
    CollisionCheckingTrait
};

use pocketmine\entity\Entity;

use pocketmine\block\Block;

use pocketmine\math\AxisAlignedBB;

class Villager extends \pocketmine\entity\Villager implements Collidable, Interactable {
    use AgeableTrait, CollisionCheckingTrait;

    public function onCollideWithEntity(Entity $entity) : void {
        // TODO: Implement onCollideWithEntity() method.
    }

    public function onCollideWithBlock(Block $block) : void {
        // TODO: Implement onCollideWithBlock() method.
    }

    public function push(AxisAlignedBB $source) : void {
        // TODO: Implement push() method.
    }

    public function onPlayerInteract(CorePlayer $player) : void {
        // TODO: Implement onPlayerInteract() method.
    }
}