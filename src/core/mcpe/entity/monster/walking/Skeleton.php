<?php

namespace core\mcpe\entity\monster\walking;

use core\CorePlayer;

use core\mcpe\entity\{
	MonsterBase,
	InventoryHolder,
	ItemHolderTrait,
	ClimbingTrait,
	CreatureBase
};

use core\mcpe\entity\object\Arrow;

use core\utils\Level;

use pocketmine\item\Item;

use pocketmine\entity\Entity;

use pocketmine\math\Vector3;

use pocketmine\event\entity\{
	EntityShootBowEvent,
	ProjectileLaunchEvent
};

use pocketmine\entity\projectile\Projectile;

use pocketmine\level\sound\LaunchSound;

use pocketmine\block\Water;

use pocketmine\level\Position;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\network\mcpe\protocol\TakeItemEntityPacket;

class Skeleton extends MonsterBase implements InventoryHolder {
	use ItemHolderTrait, ClimbingTrait;

	public const NETWORK_ID = self::SKELETON;

	public $width = 0.875, $height = 2.0;
	/** @var int */
	protected $moveTime, $attackDelay;

	protected $speed = 1.0;

	public function initEntity() : void {
		if(!isset($this->mainHand)) {
			$this->mainHand = Item::get(Item::BOW);
		} // TODO: random enchantments
		// TODO: random armour
		parent::initEntity();
	}

	public function getName() : string {
		return "Skeleton";
	}

	public function onUpdate(int $currentTick) : bool {
		if($this->isFlaggedForDespawn() or $this->closed) {
			return false;
		}
		if($this->attackTime > 0) {
			return parent::onUpdate($currentTick);
		} else {
			if($this->moveTime <= 0 and $this->isTargetValid($this->target) and !$this->target instanceof Entity) {
				$x = $this->target->x - $this->x;
				$y = $this->target->y - $this->y;
				$z = $this->target->z - $this->z;
				$diff = abs($x) + abs($z);

				if($diff > 0) {
					$this->motion->x = $this->speed * 0.15 * ($x / $diff);
					$this->motion->z = $this->speed * 0.15 * ($z / $diff);
					$this->yaw = rad2deg(-atan2($x / $diff, $z / $diff)); // TODO: desync head with body when AI improves
				}
				$this->pitch = $y == 0 ? 0 : rad2deg(-atan2($y, sqrt($x * $x + $z * $z)));

				if($this->distance($this->target) <= 0) {
					$this->target = null;
				}
			} else if($this->target instanceof Entity and $this->isTargetValid($this->target)) {
				$this->moveTime = 0;

				if($this->distance($this->target) <= 16) {
					if($this->attackDelay > 30 and mt_rand(1, 32) < 4) {
						$this->attackDelay = 0;
						$force = 1.2; // TODO: correct speed?
						$yaw = $this->yaw + mt_rand(-220, 220) / 10;
						$pitch = $this->pitch + mt_rand(-120, 120) / 10;
						$nbt = Arrow::createBaseNBT(new Vector3($this->x + (-sin($yaw / 180 * M_PI) * cos($pitch / 180 * M_PI) * 0.5), $this->y + $this->eyeHeight, $this->z + (cos($yaw / 180 * M_PI) * cos($pitch / 180 * M_PI) * 0.5)), new Vector3(), $yaw, $pitch);
						/** @var Arrow $arrow */
						$arrow = Arrow::createEntity("Arrow", $this->level, $nbt, $this);
						$ev = new EntityShootBowEvent($this, Item::get(Item::ARROW, 0, 1), $arrow, $force);

						$ev->call();

						$projectile = $ev->getProjectile();

						if($ev->isCancelled()) {
							$projectile->flagForDespawn();
						} else if($projectile instanceof Projectile) {
							$launch = new ProjectileLaunchEvent($projectile);

							$launch->call();

							if($launch->isCancelled()) {
								$projectile->flagForDespawn();
							} else {
								$projectile->setMotion(new Vector3(-sin($yaw / 180 * M_PI) * cos($pitch / 180 * M_PI) * $ev->getForce(), -sin($pitch / 180 * M_PI) * $ev->getForce(), cos($yaw / 180 * M_PI) * cos($pitch / 180 * M_PI) * $ev->getForce()));
								$projectile->spawnToAll();
								$this->level->addSound(new LaunchSound($this), $projectile->getViewers());
							}
						}
					}
					$target = $this->getSide(self::getRightSide($this->getDirection()));
					$x = $target->x - $this->x;
					$z = $target->z - $this->z;
					$diff = abs($x) + abs($z);

					if($diff > 0) {
						$this->motion->x = $this->speed * 0.15 * ($x / $diff);
						$this->motion->z = $this->speed * 0.15 * ($z / $diff);
					}
					$this->lookAt($this->target->add(0, $this->target->eyeHeight));
				} else {
					$x = $this->target->x - $this->x;
					$y = $this->target->y - $this->y;
					$z = $this->target->z - $this->z;
					$diff = abs($x) + abs($z);

					if($diff > 0) {
						$this->motion->x = $this->speed * 0.15 * ($x / $diff);
						$this->motion->z = $this->speed * 0.15 * ($z / $diff);
						$this->yaw = rad2deg(-atan2($x / $diff, $z / $diff)); // TODO: desync head with body when AI improves
					}
					$this->pitch = $y === 0 ? 0 : rad2deg(-atan2($y, sqrt($x * $x + $z * $z)));
				}
			} elseif($this->moveTime <= 0) {
				$this->moveTime = 100;
				// TODO: random target position
			}
		}
		return parent::onUpdate($currentTick);
	}

	public function entityBaseTick(int $tickDiff = 1) : bool {
		$this->checkNearEntities();

		if($this->target === null) {
			foreach($this->hasSpawned as $player) {
				if($player->isSurvival() and $this->distance($player) <= 16 and $this->hasLineOfSight($player)) {
					$this->target = $player;
				}
			}
		} else if($this->target instanceof CorePlayer) {
			if($this->target->isCreative() or !$this->target->isAlive()) {
				$this->target = null;
			}
		}
		$hasUpdate = parent::entityBaseTick($tickDiff);

		if($this->moveTime > 0) {
			$this->moveTime -= $tickDiff;
		}
		$time = $this->getLevel()->getTime() % Level::TIME_FULL;

		if(!$this->isOnFire() and ($time < Level::TIME_NIGHT or $time > Level::TIME_SUNRISE) and $this->level->getBlockSkyLightAt($this->getFloorX(), $this->getFloorY(), $this->getFloorZ()) >= 15) {
			$this->setOnFire(2);
		}
		if($this->isOnFire() and $this->level->getBlock($this, true, false) instanceof Water) { // TODO: check weather
			$this->extinguish();
		}
		$this->attackDelay += $tickDiff;
		return $hasUpdate;
	}

	public static function spawnMob(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
		// TODO: Implement spawnMob() method.
	}

	public static function spawnFromSpawner(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
		// TODO: Implement spawnFromSpawner() method.
	}

	public function onCollideWithEntity(Entity $entity) : void {
		if($entity instanceof \core\mcpe\entity\object\Item) {
			if($entity->getPickupDelay() > 0 or !$this instanceof InventoryHolder or $this->level->getDifficulty() <= Level::DIFFICULTY_EASY) {
				return;
			}
			$chance = Level::getRegionalDifficulty($this->level, $this->chunk);

			if($chance < 50) {
				return;
			}
			$item = $entity->getItem();

			if(!$this->checkItemValueToMainHand($item) and !$this->checkItemValueToOffHand($item)) {
				return;
			}
			$pk = new TakeItemEntityPacket();
			$pk->eid = $this->getId();
			$pk->target = $this->getId();

			$this->server->broadcastPacket($this->getViewers(), $pk);
			$this->setDropAll();
			$this->setPersistence(true);

			if($this->checkItemValueToMainHand($item)) {
				$this->mainHand = clone $item;
			} else if($this->checkItemValueToOffHand($item)) {
				$this->offHand = clone $item;
			}
		}
	}

	public function checkItemValueToMainHand(Item $item) : bool {
		return $this->mainHand === null;
	}

	public function checkItemValueToOffHand(Item $item) : bool {
		return false;
	}

	public function equipRandomItems() : void {
	}

	public function equipRandomArmour() : void {
		// TODO: random armour chance by difficulty
	}

	public function getXpDropAmount() : int {
		$exp = 5;

		foreach($this->getArmorInventory()->getContents() as $piece) {
			$exp += mt_rand(1, 3);
		}
		return $exp;
	}

	public function getDrops() : array {
		$drops = parent::getDrops();

		if($this->dropAll) {
			$drops = array_merge($drops, $this->armorInventory->getContents());
		} else if(mt_rand(1, 100) <= 8.5) {
			if(!empty($this->armorInventory->getContents())) {
				$drops[] = $this->armorInventory->getContents()[array_rand($this->armorInventory->getContents())];
			}
		}
		return $drops;
	}
}