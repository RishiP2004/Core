<?php

declare(strict_types = 1);

namespace core\mcpe\entity;

use core\CorePlayer;

use pocketmine\entity\{
	Creature,
	Entity
};

use pocketmine\level\Position;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\timings\Timings;

use pocketmine\math\{
	Vector3,
	AxisAlignedBB
};

use pocketmine\block\{
	Block,
	BlockIds
};

abstract class CreatureBase extends Creature implements Linkable, Collidable, Lookable {
	use SpawnableTrait, CollisionCheckingTrait, LinkableTrait;

	protected $speed = 1.0, $stepHeight = 1.0;
	/** @var Position|null $target */
	protected $target = null;

	protected $persistent = false;

	protected $moveTime = 0, $idleTime = 0;

	public function initEntity() : void {
		parent::initEntity();
		$this->setGenericFlag(self::DATA_FLAG_NO_AI, true);
	}

	public static function spawnMob(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
		return null;
	}

	public static function getRightSide(int $side) : int {
		if($side >= 0 and $side <= 5) {
			return $side ^ 0x03; // TODO: right now it gives the opposite side...
		}
		throw new \InvalidArgumentException("Invalid side $side given to getRightSide");
	}

	public function lookAround() : void {
		$entities = $this->level->getNearbyEntities($this->boundingBox->expandedCopy(8,2,8), $this);
		$entities = array_filter($entities,function(Entity $entity){
			if($entity->isAlive() or !$entity->isFlaggedForDespawn() and $entity instanceof Player) {
				return true;
			}
			return false;
		});
		$yaw = $this->yaw;
		$pitch = $this->pitch;

		if(!empty($entities) and mt_rand(1,3) === 1) {
			/** @var CorePlayer $player */
			$player = $entities[array_rand($entities)];

			$this->lookAt($player->asVector3()->add(0, $player->height));
		}else{
			$yaw = mt_rand(0, 1) ? $yaw + mt_rand(15, 45) : $yaw - mt_rand(15, 45);

			if($yaw > 360){
				$yaw = 360;
			} else if($yaw < 0) {
				$yaw = 0;
			}
			$pitch = mt_rand(0, 1) ? $pitch + mt_rand(10, 20) : $pitch - mt_rand(10, 20);

			if($pitch > 60) {
				$pitch = 60;
			} else if($pitch < -60) {
				$pitch = -60;
			}
		}
		$this->setRotation($yaw, $pitch);
	}

	public function move(float $dx, float $dy, float $dz) : void {
		$this->blocksAround = null;
		Timings::$entityMoveTimer->startTiming();
		$movX = $dx;
		$movY = $dy;
		$movZ = $dz;

		if($this->keepMovement) {
			$this->boundingBox->offset($dx, $dy, $dz);
		} else {
			$this->ySize *= 0.4;
			$axisalignedbb = clone $this->boundingBox;
			$list = $this->level->getCollisionCubes($this, $this->boundingBox->addCoord($dx, $dy, $dz), false);

			foreach($list as $bb) {
				$dy = $bb->calculateYOffset($this->boundingBox, $dy);
			}
			$this->boundingBox->offset(0, $dy, 0);

			$fallingFlag = ($this->onGround or ($dy != $movY and $movY < 0));

			foreach($list as $bb) {
				$dx = $bb->calculateXOffset($this->boundingBox, $dx);
			}
			$this->boundingBox->offset($dx, 0, 0);

			foreach($list as $bb) {
				$dz = $bb->calculateZOffset($this->boundingBox, $dz);
			}
			$this->boundingBox->offset(0, 0, $dz);

			if($this->stepHeight > 0 and $fallingFlag and $this->ySize < 0.05 and ($movX != $dx or $movZ != $dz)) {
				$cx = $dx;
				$cy = $dy;
				$cz = $dz;
				$dx = $movX;
				$dy = $this->stepHeight;
				$dz = $movZ;
				$axisalignedbb1 = clone $this->boundingBox;

				$this->boundingBox->setBB($axisalignedbb);

				$list = $this->level->getCollisionCubes($this, $this->boundingBox->addCoord($dx, $dy, $dz), false);

				foreach($list as $bb) {
					$dy = $bb->calculateYOffset($this->boundingBox, $dy);
				}
				$this->boundingBox->offset(0, $dy, 0);

				foreach($list as $bb) {
					$dx = $bb->calculateXOffset($this->boundingBox, $dx);
				}
				$this->boundingBox->offset($dx, 0, 0);

				foreach($list as $bb) {
					$dz = $bb->calculateZOffset($this->boundingBox, $dz);
				}
				$this->boundingBox->offset(0, 0, $dz);

				if(($cx ** 2 + $cz ** 2) >= ($dx ** 2 + $dz ** 2)) {
					$dx = $cx;
					$dy = $cy;
					$dz = $cz;

					$this->boundingBox->setBB($axisalignedbb1);
				} else {
					$block = $this->level->getBlock($this->getSide(Vector3::SIDE_DOWN));
					$blockBB = $block->getBoundingBox() ?? new AxisAlignedBB($block->x, $block->y, $block->z, $block->x + 1, $block->y + 1, $block->z + 1);
					$this->ySize += $blockBB->maxY - $blockBB->minY;
				}
			}
		}
		$this->x = ($this->boundingBox->minX + $this->boundingBox->maxX) / 2;
		$this->y = $this->boundingBox->minY - $this->ySize;
		$this->z = ($this->boundingBox->minZ + $this->boundingBox->maxZ) / 2;

		$this->checkChunks();
		$this->checkBlockCollision();
		$this->checkGroundState($movX, $movY, $movZ, $dx, $dy, $dz);
		$this->updateFallState($dy, $this->onGround);

		if($movX != $dx) {
			$this->motion->x = 0;
		}
		if($movY != $dy) {
			$this->motion->y = 0;
		}
		if($movZ != $dz) {
			$this->motion->z = 0;
		}
		//TODO: Vehicle collision events
		Timings::$entityMoveTimer->stopTiming();
	}

	public function hasLineOfSight(Entity $entity) : bool {
		$distance = (int) $this->add(0, $this->eyeHeight)->distance($entity);

		if($distance > 1) {
			$blocksBetween = $this->getLineOfSight($distance, 0, [
				BlockIds::AIR => BlockIds::AIR,
				BlockIds::WATER => BlockIds::WATER,
				BlockIds::LAVA => BlockIds::LAVA
			]);
			return empty(array_filter($blocksBetween, function(Block $block) {
				return !in_array($block->getId(), [BlockIds::AIR, BlockIds::WATER, BlockIds::LAVA]);
			}));
		}
		return true;
	}

	public function getTarget() : ?Position {
		return $this->target;
	}

	public function setTarget(?Position $target) : self {
		$this->target = $target;

		if($target instanceof Entity or is_null($target)) {
			$this->setTargetEntity($target);
		}
		return $this;
	}

	public function getSpeed() : float {
		return $this->speed;
	}

	public function setSpeed(float $speed) : self {
		$this->speed = $speed;
		return $this;
	}

	public function isPersistent() : bool {
		return $this->persistent;
	}

	public function setPersistence(bool $persistent) : self {
		$this->persistent = $persistent;
		return $this;
	}

	public function onPlayerLook(CorePlayer $player) : void {
		// TODO: Implement onPlayerLook() method.
	}

	public function onCollideWithEntity(Entity $entity) : void {
	}

	public function onCollideWithBlock(Block $block) : void {
	}

	public function push(AxisAlignedBB $source) : void {
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