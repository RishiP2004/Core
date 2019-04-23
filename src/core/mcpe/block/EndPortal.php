<?php

declare(strict_types = 1);

namespace core\mcpe\block;

use core\Core;
use core\CorePlayer;

use core\mcpe\task\DelayedCrossDimensionTeleport;

use pocketmine\block\{
    Solid,
    Block
};

use pocketmine\item\Item;

use pocketmine\entity\Entity;

use pocketmine\level\Level;

use pocketmine\network\mcpe\protocol\types\DimensionIds;

class EndPortal extends Solid {
    protected $id = Block::END_PORTAL;

    public function __construct($meta = 0) {
        $this->meta = $meta;
    }

    public function getName() : string {
        return "End Portal";
    }

    public function getLightLevel() : int {
        return 1;
    }

    public function getHardness() : float {
        return -1;
    }

    public function getBlastResistance() : float {
        return 18000000;
    }

    public function isBreakable(Item $item) : bool {
        return false;
    }

    public function canPassThrough() : bool {
        return true;
    }

    public function hasEntityCollision() : bool {
        return true;
    }

    public function onEntityCollide(Entity $entity) : void {
        if($entity->getLevel()->getSafeSpawn()->distance($entity->asVector3()) <= 0.1) {
            return;
        }
        if(!isset(Core::getInstance()->getMCPE()->onPortal[$entity->getId()])) {
            Core::getInstance()->getMCPE()->onPortal[$entity->getId()] = true;

            if($entity instanceof CorePlayer) {
                if($entity->getLevel() instanceof Level) {
                    if($entity->getLevel()->getName() !== Core::getInstance()->getMCPE()::$endName) {
                        Core::getInstance()->getScheduler()->scheduleDelayedTask(new DelayedCrossDimensionTeleport($entity, DimensionIds::THE_END, Core::getInstance()->getMCPE()::$endLevel->getSafeSpawn()), 1);
                    } else {
                        Core::getInstance()->getScheduler()->scheduleDelayedTask(new DelayedCrossDimensionTeleport($entity, DimensionIds::OVERWORLD, Core::getInstance()->getMCPE()::$overworldLevel->getSafeSpawn()), 1);
                    }
                }
            }
            //TODO: Add mob teleportation
        }
        return;
    }
}