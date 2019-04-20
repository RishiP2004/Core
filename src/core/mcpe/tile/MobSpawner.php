<?php

namespace core\mcpe\tile;

use core\Core;

use core\mcpe\Addon;

use core\mcpe\entity\CreatureBase;

use pocketmine\tile\Spawnable;

use pocketmine\math\AxisAlignedBB;

use pocketmine\utils\TextFormat;

use pocketmine\entity\EntityIds;

use pocketmine\level\{
	Level,
	Position
};

use pocketmine\entity\{
	Living,
	Entity
};

use pocketmine\nbt\tag\{
	CompoundTag,
	IntTag,
	ShortTag,
	FloatTag
};

abstract class MobSpawner extends Spawnable implements Addon {
	public const IS_MOVABLE = "isMovable";
	public const DELAY = "Delay";
	public const MAX_NEARBY_ENTITIES = "MaxNearbyEntities";
	public const MAX_SPAWN_DELAY = "MaxSpawnDelay";
	public const MIN_SPAWN_DELAY = "MinSawnDelay";
	public const REQUIRED_PLAYER_RANGE = "RequiredPlayerRange";
	public const SPAWN_COUNT = "SpawnCount";
	public const SPAWN_RANGE = "SpawnRange";
	public const ENTITY_ID = "EntityId";
	public const DISPLAY_ENTITY_HEIGHT = "DisplayEntityHeight";
	public const DISPLAY_ENTITY_SCALE = "DisplayEntityScale";
	public const DISPLAY_ENTITY_WIDTH = "DisplayEntityWidth";

	protected $spawnRange = 4, $delay = -1, $maxNearbyEntities = 6, $requiredPlayerRange = 16, $minSpawnDelay = 200, $maxSpawnDelay = 800, $spawnCount = 4, $tier;
	/** @var AxisAlignedBB|null $spawnArea */
	protected $spawnArea;

	protected $isMovable = false;

	protected $entityId = -1;

	protected $displayHeight = 0.9, $displayScale = 0.5, $displayWidth = 0.3;

    public function getName() : string {
        if($this->getEntityId() === 0) {
            $name = TextFormat::AQUA . "Monster Spawner";
        } else {
            if(Core::getInstance()->getNetwork()->getServerFromIp(Core::getInstance()->getServer()->getIp())->getName() === "Factions") {
                $name = TextFormat::AQUA . ucfirst(self::TYPES[$this->getCleanedNBT()->namedTag->EntityId] ?? "monster") . " Spawner \n" . TextFormat::GOLD . " Tier:" . $this->getTier();
            } else {
                $name = TextFormat::AQUA . ucfirst(self::TYPES[$this->getCleanedNBT()->namedTag->EntityId] ?? "monster") . " Spawner";
            }
        }
        return $name;
    }

	public function getEntityId() : int {
		return $this->entityId;
	}

	public function setEntityId(int $eid) : self {
		$this->entityId = $eid;
		$this->delay = mt_rand($this->minSpawnDelay, $this->maxSpawnDelay);

		$this->onChanged();
		$this->scheduleUpdate();
		return $this;
	}

	public function setMinSpawnDelay(int $minDelay) : self {
		if($minDelay < $this->maxSpawnDelay) {
			$this->minSpawnDelay = $minDelay;
		}
		return $this;
	}

	public function setMaxSpawnDelay(int $maxDelay) : self {
		if($this->minSpawnDelay < $maxDelay and $maxDelay !== 0) {
			$this->maxSpawnDelay = $maxDelay;
		}
		return $this;
	}

	public function setSpawnDelay(int $delay) : self {
		if($delay < $this->maxSpawnDelay and $delay > $this->minSpawnDelay) {
			$this->delay = $delay;
		}
		return $this;
	}

	public function setRequiredPlayerRange(int $range) : self {
		if($range < 0) {
			$range = 0;
		}
		$this->requiredPlayerRange = $range;
		return $this;
	}

	public function setMaxNearbyEntities(int $count) : self {
		$this->maxNearbyEntities = $count;
		return $this;
	}

	public function setMovable(bool $isMovable = true) : self {
		$this->isMovable = $isMovable;
		return $this;
	}

	public function isMovable() : bool {
		return $this->isMovable;
	}

	public function getTier() {
		return $this->tier;
	}

	public function setTier(int $tier) : self {
		$this->tier = $tier;

		$this->onChanged();
		$this->scheduleUpdate();
		return $this;
	}

	public function onUpdate() : bool {
		if($this->isClosed() or $this->entityId < EntityIds::CHICKEN) { // TODO: are there entities with ids less than 10?
			return false;
		}
		if(--$this->delay === 0) {
			$this->delay = mt_rand($this->minSpawnDelay, $this->maxSpawnDelay);
			$valid = false;

			foreach($this->level->getPlayers() as $player) {
				if($this->distance($player) <= $this->requiredPlayerRange) {
					$valid = true;
					break;
				}
			}
			foreach(Core::getInstance()->getMCPE()->getRegisteredEntities() as $class => $array) {
				if($class instanceof CreatureBase and $class::NETWORK_ID === $this->entityId) {
					if($valid and count(self::getAreaEntities($this->spawnArea, $this->level, $class)) < $this->maxNearbyEntities) {
						$spawned = 0;

						while($spawned < $this->spawnCount) {
							$entity = $class::spawnFromSpawner($this->getRandomSpawnPos());

							if($entity !== null) {
								$spawned++;
							}
						}
					}
				}
			}
		} else if($this->delay === -1) {
			$this->delay = mt_rand($this->minSpawnDelay, $this->maxSpawnDelay);
			$this->entityId = mt_rand(EntityIds::CHICKEN, EntityIds::FISH);

			$this->onChanged();
		}
		$this->scheduleUpdate();
		return true;
	}

	protected static function getAreaEntities(AxisAlignedBB $bb, Level $level, string $type = Living::class) {
		$nearby = [];
		$minX = ((int) floor($bb->minX)) >> 4; // TODO: check if this is right
		$maxX = ((int) floor($bb->maxX)) >> 4;
		$minZ = ((int) floor($bb->minZ)) >> 4;
		$maxZ = ((int) floor($bb->maxZ)) >> 4;

		for($x = $minX; $x <= $maxX; ++$x) {
			for($z = $minZ; $z <= $maxZ; ++$z) {
				foreach($level->getChunkEntities($x, $z) as $entity) {
					/** @var Entity|null $entity */
					if($entity instanceof $type and $entity->boundingBox->intersectsWith($bb)) {
						$nearby[] = $entity;
					}
				}
			}
		}
		return $nearby;
	}

	protected function getRandomSpawnPos() : Position {
		$x = mt_rand($this->spawnArea->minX, $this->spawnArea->maxX);
		$y = mt_rand($this->spawnArea->minY, $this->spawnArea->maxY);
		$z = mt_rand($this->spawnArea->minZ, $this->spawnArea->maxZ);
		return new Position($x + 0.5, $y, $z + 0.5, $this->level);
	}

	protected function readSaveData(CompoundTag $nbt) : void {
		if($nbt->hasTag(self::ENTITY_ID, IntTag::class)) {
			$this->entityId = $nbt->getInt(self::ENTITY_ID);
		}
		if($nbt->hasTag(self::SPAWN_COUNT, ShortTag::class)) {
			$this->spawnCount = $nbt->getShort(self::SPAWN_COUNT);
		}
		if($nbt->hasTag(self::SPAWN_RANGE, ShortTag::class)) {
			$this->spawnRange = $nbt->getShort(self::SPAWN_RANGE);
		}
		$this->spawnArea = new AxisAlignedBB($this->x - $this->spawnRange, $this->y - 1, $this->z - $this->spawnRange, $this->x + $this->spawnRange, $this->y + 1, $this->z + $this->spawnRange);

		if($nbt->hasTag(self::DELAY, ShortTag::class)) {
			$this->delay = $nbt->getShort(self::DELAY);
		}
		if($nbt->hasTag(self::MIN_SPAWN_DELAY, ShortTag::class)) {
			$this->minSpawnDelay = $nbt->getShort(self::MIN_SPAWN_DELAY);
		}
		if($nbt->hasTag(self::MAX_SPAWN_DELAY, ShortTag::class)) {
			$this->maxSpawnDelay = $nbt->getShort(self::MAX_SPAWN_DELAY);
		}
		if($nbt->hasTag(self::MAX_NEARBY_ENTITIES, ShortTag::class)) {
			$this->maxNearbyEntities = $nbt->getShort(self::MAX_NEARBY_ENTITIES);
		}
		if($nbt->hasTag(self::REQUIRED_PLAYER_RANGE, ShortTag::class)) {
			$this->requiredPlayerRange = $nbt->getShort(self::REQUIRED_PLAYER_RANGE);
		}
		if($nbt->hasTag(self::DISPLAY_ENTITY_HEIGHT, FloatTag::class)) {
			$this->displayHeight = $nbt->getFloat(self::DISPLAY_ENTITY_HEIGHT);
		}
		if($nbt->hasTag(self::DISPLAY_ENTITY_WIDTH, FloatTag::class)) {
			$this->displayHeight = $nbt->getFloat(self::DISPLAY_ENTITY_WIDTH);
		}
		if($nbt->hasTag(self::DISPLAY_ENTITY_SCALE, FloatTag::class)) {
			$this->displayHeight = $nbt->getFloat(self::DISPLAY_ENTITY_SCALE);
		}
	}

	protected function writeSaveData(CompoundTag $nbt) : void {
		$this->addAdditionalSpawnData($nbt);
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void {
		$nbt->setByte(self::IS_MOVABLE, (int)$this->isMovable);
		$nbt->setShort(self::DELAY, $this->delay);
		$nbt->setShort(self::MAX_NEARBY_ENTITIES, $this->maxNearbyEntities);
		$nbt->setShort(self::MAX_SPAWN_DELAY, $this->maxSpawnDelay);
		$nbt->setShort(self::MIN_SPAWN_DELAY, $this->minSpawnDelay);
		$nbt->setShort(self::REQUIRED_PLAYER_RANGE, $this->requiredPlayerRange);
		$nbt->setShort(self::SPAWN_COUNT, $this->spawnCount);
		$nbt->setShort(self::SPAWN_RANGE, $this->spawnRange);
		$nbt->setInt(self::ENTITY_ID, $this->entityId);
		$nbt->setFloat(self::DISPLAY_ENTITY_HEIGHT, $this->displayHeight);
		$nbt->setFloat(self::DISPLAY_ENTITY_WIDTH, $this->displayWidth);
		$nbt->setFloat(self::DISPLAY_ENTITY_SCALE, $this->displayScale);
		$this->scheduleUpdate();
	}
}