<?php

namespace core\mcpe\block;

use core\Core;

use core\mcpe\task\DelayedCrossDimensionTeleport;

use pocketmine\Player;

use pocketmine\block\{
    Transparent,
    Block,
    BlockToolType,
    Air
};

use pocketmine\item\Item;

use pocketmine\math\Vector3;

use pocketmine\entity\Entity;

use pocketmine\level\Level;

use pocketmine\network\mcpe\protocol\types\DimensionIds;

class NetherPortal extends Transparent {
    protected $id = Block::PORTAL;

    public function __construct($meta = 0) {
        $this->meta = $meta;
    }

    public function getToolType() : int {
        return BlockToolType::TYPE_PICKAXE;
    }

    public function canPassThrough() : bool {
        return true;
    }

    public function hasEntityCollision() : bool {
        return true;
    }

    public function onBreak(Item $item, Player $player = null) : bool {
        $block = $this;
        $temporalVector = new Vector3(0, 0, 0);

        if($this->getLevel()->getBlock($temporalVector->setComponents($block->x - 1, $block->y, $block->z))->getId() === Block::PORTAL or $this->getLevel()->getBlock($temporalVector->setComponents($block->x + 1, $block->y, $block->z))->getId() === Block::PORTAL) {
            for($x = $block->x; $this->getLevel()->getBlock($temporalVector->setComponents($x, $block->y, $block->z))->getId() === Block::PORTAL; $x++) {
                for($y = $block->y; $this->getLevel()->getBlock($temporalVector->setComponents($x, $y, $block->z))->getId() === Block::PORTAL; $y++) {
                    $this->getLevel()->setBlock($temporalVector->setComponents($x, $y, $block->z), new Air());
                }
                for($y = $block->y - 1; $this->getLevel()->getBlock($temporalVector->setComponents($x, $y, $block->z))->getId() === Block::PORTAL; $y--) {
                    $this->getLevel()->setBlock($temporalVector->setComponents($x, $y, $block->z), new Air());
                }
            }
            for($x = $block->x - 1; $this->getLevel()->getBlock($temporalVector->setComponents($x, $block->y, $block->z))->getId() === Block::PORTAL; $x--) {
                for($y = $block->y; $this->getLevel()->getBlock($temporalVector->setComponents($x, $y, $block->z))->getId() === Block::PORTAL; $y++) {
                    $this->getLevel()->setBlock($temporalVector->setComponents($x, $y, $block->z), new Air());
                }
                for($y = $block->y - 1; $this->getLevel()->getBlock($temporalVector->setComponents($x, $y, $block->z))->getId() === Block::PORTAL; $y--) {
                    $this->getLevel()->setBlock($temporalVector->setComponents($x, $y, $block->z), new Air());
                }
            }
        } else {
            for($z = $block->z; $this->getLevel()->getBlock($temporalVector->setComponents($block->x, $block->y, $z))->getId() === Block::PORTAL; $z++) {
                for($y = $block->y; $this->getLevel()->getBlock($temporalVector->setComponents($block->x, $y, $z))->getId() === Block::PORTAL; $y++) {
                    $this->getLevel()->setBlock($temporalVector->setComponents($block->x, $y, $z), new Air());
                }
                for($y = $block->y - 1; $this->getLevel()->getBlock($temporalVector->setComponents($block->x, $y, $z))->getId() === Block::PORTAL; $y--) {
                    $this->getLevel()->setBlock($temporalVector->setComponents($block->x, $y, $z), new Air());
                }
            }
            for($z = $block->z - 1; $this->getLevel()->getBlock($temporalVector->setComponents($block->x, $block->y, $z))->getId() === Block::PORTAL; $z--) {
                for($y = $block->y; $this->getLevel()->getBlock($temporalVector->setComponents($block->x, $y, $z))->getId() === Block::PORTAL; $y++) {
                    $this->getLevel()->setBlock($temporalVector->setComponents($block->x, $y, $z), new Air());
                }
                for($y = $block->y - 1; $this->getLevel()->getBlock($temporalVector->setComponents($block->x, $y, $z))->getId() === Block::PORTAL; $y--) {
                    $this->getLevel()->setBlock($temporalVector->setComponents($block->x, $y, $z), new Air());
                }
            }
        }
        return true;
    }

    public function place(Item $item, Block $block, Block $target, int $face, Vector3 $facePos, Player $player = null) : bool {
        if($player instanceof Player) {
            $this->meta = $player->getDirection() & 0x01;
        }
        $this->getLevel()->setBlock($block, $this, true, true);

        return true;
    }

    public function onEntityCollide(Entity $entity) : void {
        if($entity->getLevel()->getSafeSpawn()->distance($entity->asVector3()) <= 0.1) {
            return;
        }
        if(!isset(Core::getInstance()->getMCPE()::$onPortal[$entity->getId()])) {
            Core::getInstance()->getMCPE()::$onPortal[$entity->getId()] = true;

            if($entity instanceof Player) {
                if($entity->getLevel() instanceof Level) {
                    if($entity->getLevel()->getName() !== Core::getInstance()->getMCPE()::$netherName) {
                        $gamemode = $entity->getGamemode();
                        $positionNether = Core::getInstance()->getMCPE()::$netherLevel->getSafeSpawn();

                        if($gamemode == Player::SURVIVAL or $gamemode == Player::ADVENTURE) {
                            Core::getInstance()->getScheduler()->scheduleDelayedTask(new DelayedCrossDimensionTeleport($entity, DimensionIds::NETHER, $positionNether), 20 * 4);
                        } else {
                            Core::getInstance()->getScheduler()->scheduleDelayedTask(new DelayedCrossDimensionTeleport($entity, DimensionIds::NETHER, $positionNether), 1);
                        }
                    } else {
                        $gamemode = $entity->getGamemode();
                        $positionOverworld = Core::getInstance()->getMCPE()::$overworldLevel->getSafeSpawn();

                        if($gamemode === Player::SURVIVAL or $gamemode === Player::ADVENTURE) {
                            Core::getInstance()->getScheduler()->scheduleDelayedTask(new DelayedCrossDimensionTeleport($entity, DimensionIds::OVERWORLD, $positionOverworld), 20 * 4);
                        } else {
                            Core::getInstance()->getScheduler()->scheduleDelayedTask(new DelayedCrossDimensionTeleport($entity, DimensionIds::OVERWORLD, $positionOverworld), 1);
                        }
                    }
                }
            }
            // TODO: Add mob teleportation
        }
    }
}