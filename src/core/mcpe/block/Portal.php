<?php

declare(strict_types = 1);

namespace core\mcpe\block;

use core\Core;

use core\utils\Math;

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

class Portal extends Transparent {
	protected $id = Block::PORTAL;

	public function __construct($meta = 0) {
		$this->meta = $meta;
	}

	public function getName() : string {
		return "Portal";
	}

	public function getHardness() : float {
		return -1;
	}

	public function getResistance() : float {
		return 0;
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

	public function onEntityCollide(Entity $entity) : void {
		if($entity->getLevel()->getSafeSpawn()->distance($entity->asVector3()) <= 0.1) {
			return;
		}
		if(!isset(Core::getInstance()->getMCPE()::$onPortal[$entity->getId()])) {
			Core::getInstance()->getMCPE()::$onPortal[$entity->getId()] = true;

			if($entity instanceof Player) {
				if($entity->getLevel() instanceof Level) {
					if($entity->getLevel()->getName() !== Core::getInstance()->getMCPE()::$netherName) {
						$gm = $entity->getGamemode();
						$posNether = Core::getInstance()->getMCPE()::$netherLevel->getSafeSpawn();

						if(Core::getInstance()->getMCPE()::$vanillaNetherTransfer) {
							$x = (int) ceil($entity->getX() / 8);
							$y = (int) ceil($entity->getY() / 8);
							$z = (int) ceil($entity->getZ() / 8);

							if(!Core::getInstance()->getMCPE()::$netherLevel->getBlockAt($x, $y - 1, $z)->isSolid() or Core::getInstance()->getMCPE()::$netherLevel->getBlockAt($x, $y, $z)->isSolid() or Core::getInstance()->getMCPE()::$netherLevel->getBlockAt($x, $y + 1, $z)->isSolid()) {
								for($y2 = 125; $y2 >= 0; $y2--) {
									if(Core::getInstance()->getMCPE()::$netherLevel->getBlockAt($x, $y2 - 1, $z, true, false)->isSolid() and !Core::getInstance()->getMCPE()::$netherLevel->getBlockAt($x, $y2, $z, true, false)->isSolid() and !Core::getInstance()->getMCPE()::$netherLevel->getBlockAt($x, $y2 + 1, $z, true, false)->isSolid()) {
										break;
									}
								}
								if($y2 <= 0) {
									$y = mt_rand(10, 125);
								} else {
									$y = $y2;
								}
							}
							if(Math::vector3XZDistance($posNether, $entity->asVector3()) <= 0.1) {
								return;
							}
							$posNether->setComponents($x, $y, $z);
						}
						if($gm === Player::SURVIVAL or $gm === Player::ADVENTURE) {
							Core::getInstance()->getScheduler()->scheduleDelayedTask(new DelayedCrossDimensionTeleport($entity, DimensionIds::NETHER, $posNether), 20 * 4);
						} else {
							Core::getInstance()->getScheduler()->scheduleDelayedTask(new DelayedCrossDimensionTeleport($entity, DimensionIds::NETHER, $posNether), 1);
						}
					} else {
						$gm = $entity->getGamemode();
						$posOverworld = Core::getInstance()->getMCPE()::$overworldLevel->getSafeSpawn();

						if(Core::getInstance()->getMCPE()::$vanillaNetherTransfer) {
							$x = (int) ceil($entity->getX() * 8);
							$y = (int) ceil($entity->getY() * 8);
							$z = (int) ceil($entity->getZ() * 8);

							if(!Core::getInstance()->getMCPE()::$overworldLevel->getBlockAt($x, $y - 1, $z)->isSolid() or Core::getInstance()->getMCPE()::$overworldLevel->getBlockAt($x, $y, $z)->isSolid() or Core::getInstance()->getMCPE()::$overworldLevel->getBlockAt($x, $y + 1, $z)->isSolid()) {
								for($y2 = 0; $y2 <= Level::Y_MAX; $y2++) {
									if(Core::getInstance()->getMCPE()::$overworldLevel->getBlockAt($x, $y2 - 1, $z, true, false)->isSolid() and !Core::getInstance()->getMCPE()::$overworldLevel->getBlockAt($x, $y2, $z, true, false)->isSolid() and !Core::getInstance()->getMCPE()::$overworldLevel->getBlockAt($x, $y2 + 1, $z, true, false)->isSolid()) {
										break;
									}
								}
								if($y2 >= Level::Y_MAX) {
									$y = mt_rand(10, Level::Y_MAX);
								} else {
									$y = $y2;
								}
							}
							if(Math::vector3XZDistance($posOverworld, $entity->asVector3()) <= 0.1) {
								return;
							}
							$posOverworld->setComponents($x, $y, $z);
						}
						if($gm === Player::SURVIVAL or $gm === Player::ADVENTURE) {
							Core::getInstance()->getScheduler()->scheduleDelayedTask(new DelayedCrossDimensionTeleport($entity, DimensionIds::OVERWORLD, $posOverworld), 20 * 4);
						} else {
							Core::getInstance()->getScheduler()->scheduleDelayedTask(new DelayedCrossDimensionTeleport($entity, DimensionIds::OVERWORLD, $posOverworld), 1);
						}
					}
				}
			}
			//TODO: Add mob teleportation
		}
	}

	public function place(Item $item, Block $block, Block $target, int $face, Vector3 $facePos, Player $player = null) : bool {
		if($player instanceof Player) {
			$this->meta = $player->getDirection() & 0x01;
		}
		$this->getLevel()->setBlock($block, $this, true, true);
		return true;
	}

	public function onBreak(Item $item, Player $player = null) : bool {
		if($this->getSide(Vector3::SIDE_WEST) instanceof Portal or $this->getSide(Vector3::SIDE_EAST) instanceof Portal) {
			for($x = $this->x; $this->getLevel()->getBlockIdAt($x, $this->y, $this->z) === Block::PORTAL; $x++) {
				for($y = $this->y; $this->getLevel()->getBlockIdAt($x, $y, $this->z) === Block::PORTAL; $y++) {
					$this->getLevel()->setBlock(new Vector3($x, $y, $this->z), new Air());
				}
				for($y = $this->y - 1; $this->getLevel()->getBlockIdAt($x, $y, $this->z) === Block::PORTAL; $y--) {
					$this->getLevel()->setBlock(new Vector3($x, $y, $this->z), new Air());
				}
			}
			for($x = $this->x - 1; $this->getLevel()->getBlockIdAt($x, $this->y, $this->z) === Block::PORTAL; $x--) {
				for($y = $this->y; $this->getLevel()->getBlockIdAt($x, $y, $this->z) === Block::PORTAL; $y++ ){
					$this->getLevel()->setBlock(new Vector3($x, $y, $this->z), new Air());
				}
				for($y = $this->y - 1; $this->getLevel()->getBlockIdAt($x, $y, $this->z) === Block::PORTAL; $y--) {
					$this->getLevel()->setBlock(new Vector3($x, $y, $this->z), new Air());
				}
			}
		} else {
			for($z = $this->z; $this->getLevel()->getBlockIdAt($this->x, $this->y, $z) === Block::PORTAL; $z++) {
				for($y = $this->y; $this->getLevel()->getBlockIdAt($this->x, $y, $z) === Block::PORTAL; $y++) {
					$this->getLevel()->setBlock(new Vector3($this->x, $y, $z), new Air());
				}
				for($y = $this->y - 1; $this->getLevel()->getBlockIdAt($this->x, $y, $z) === Block::PORTAL; $y--) {
					$this->getLevel()->setBlock(new Vector3($this->x, $y, $z), new Air());
				}
			}
			for($z = $this->z - 1; $this->getLevel()->getBlockIdAt($this->x, $this->y, $z) === Block::PORTAL; $z--) {
				for($y = $this->y; $this->getLevel()->getBlockIdAt($this->x, $y, $z) === Block::PORTAL; $y++){
					$this->getLevel()->setBlock(new Vector3($this->x, $y, $z), new Air());
				}
				for($y = $this->y - 1; $this->getLevel()->getBlockIdAt($this->x, $y, $z) === Block::PORTAL; $y--) {
					$this->getLevel()->setBlock(new Vector3($this->x, $y, $z), new Air());
				}
			}
		}
		return true;
	}

	public function getDrops(Item $item) : array {
		return [];
	}
}