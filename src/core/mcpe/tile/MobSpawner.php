<?php

namespace core\mcpe\tile;

use core\Core;
use core\CorePlayer;

use core\mcpe\Addon;

use pocketmine\tile\Spawnable;

use pocketmine\level\Level;

use pocketmine\nbt\tag\{
    CompoundTag,
    StringTag,
    ShortTag,
    IntTag
};

use pocketmine\item\Item;

use pocketmine\entity\Entity;

use pocketmine\utils\TextFormat;

abstract class MobSpawner extends Spawnable implements Addon {
    /** @var  CompoundTag */
    private $nbt;

    public function __construct(Level $level, CompoundTag $nbt) {
		if($nbt->hasTag("SpawnCount", ShortTag::class) or $nbt->hasTag("EntityId", StringTag::class)) {
            $nbt->removeTag("EntityId");
            $nbt->removeTag("SpawnCount");
            $nbt->removeTag("SpawnRange");
            $nbt->removeTag("MinSpawnDelay");
            $nbt->removeTag("MaxSpawnDelay");
            $nbt->removeTag("Delay");
        }
        if(!$nbt->hasTag("EntityId", IntTag::class)) {
            $nbt->setInt("EntityId", 0);
        }
        if(!$nbt->hasTag("SpawnCount", IntTag::class)) {
            $nbt->setInt("SpawnCount", self::SPAWN_COUNT);
        }
        if(!$nbt->hasTag("SpawnRange", IntTag::class)) {
            $nbt->setInt("SpawnRange", self::SPAWN_RANGE);
        }
        if(!$nbt->hasTag("MinSpawnDelay", IntTag::class)) {
            $nbt->setInt("MinSpawnDelay", self::MIN_SPAWN_DELAY);
        }
        if(!$nbt->hasTag("MaxSpawnDelay", IntTag::class)) {
            $nbt->setInt("MaxSpawnDelay", self::MAX_SPAWN_DELAY);
        }
        if(!$nbt->hasTag("Delay", IntTag::class)) {
            $nbt->setInt("Delay", mt_rand($nbt->getInt("MinSpawnDelay"), $nbt->getInt("MaxSpawnDelay")));
        }
        parent::__construct($level, $nbt);
		
        if($this->getEntityId() > 0) {
            $this->scheduleUpdate();
        }
    }

    public function getName() : string {
        if($this->getEntityId() === 0) {
            $name = TextFormat::AQUA . "monster Spawner";
        } else {
            if(Core::getInstance()->getNetwork()->getServerFromIp(Core::getInstance()->getServer()->getIp())->getName() === "Factions") {
                $name = TextFormat::AQUA . ucfirst(self::TYPES[$this->getCleanedNBT()->namedTag->EntityId] ?? "monster") . " Spawner \n" . TextFormat::GOLD . " Tier:" . $this->getTier();
            } else {
                $name = TextFormat::AQUA . ucfirst(self::TYPES[$this->getCleanedNBT()->namedTag->EntityId] ?? "monster") . " Spawner";
            }
        }
        return $name;
    }

    public function getNBT() : CompoundTag {
        return $this->nbt;
    }

    public function getEntityId() : int {
        return $this->getNBT()->getInt("EntityId");
    }

    public function setEntityId(int $entityId) {
        $this->getNBT()->setInt("EntityId", $entityId);
        $this->onChanged();
        $this->scheduleUpdate();
    }

    public function getSpawnCount() : int {
        return $this->getNBT()->getInt("SpawnCount");
    }

    public function setSpawnCount(int $spawnCount) {
        $this->getNBT()->setInt("SpawnCount", $spawnCount);
    }

    public function getSpawnRange() : int {
        return $this->getNBT()->getInt("SpawnRange");
    }

    public function setSpawnRange(int $spawnRange) {
        $this->getNBT()->setInt("SpawnRange", $spawnRange);
    }

    public function getMinSpawnDelay() : int {
        return $this->getNBT()->getInt("MinSpawnDelay");
    }

    public function setMinSpawnDelay(int $minSpawnDelay) {
        $this->getNBT()->setInt("MinSpawnDelay", $minSpawnDelay);
    }

    public function getMaxSpawnDelay() : int {
        return $this->getNBT()->getInt("MaxSpawnDelay");
    }

    public function setMaxSpawnDelay(int $maxSpawnDelay) {
        $this->getNBT()->setInt("MaxSpawnDelay", $maxSpawnDelay);
    }

    public function getDelay() {
        return $this->getNBT()->getInt("Delay");
    }

    public function setDelay(int $delay) {
		$this->getNBT()->setInt("Delay", $delay);
	}

	public function getTier() {
        return $this->getNBT()->getInt("Tier");
    }

    public function onUpdate() : bool {
        if($this->closed === true) {
            return false;
        }
        $this->timings->startTiming();

        if($this->getDelay() <= 0) {
            $success = 0;

            for($i = 0; $i < $this->getSpawnCount(); $i++) {
                $pos = $this->add(mt_rand() / mt_getrandmax() * $this->getSpawnRange(), mt_rand(-1, 1), mt_rand() / mt_getrandmax() * $this->getSpawnRange());
                $target = $this->getLevel()->getBlock($pos);

                if($target->getId() == Item::AIR) {
                    $success++;

                    $entity = Entity::createEntity($this->getEntityId(), $this->getLevel(), Entity::createBaseNBT($target->add(0.5, 0, 0.5), null, lcg_value() * 360, 0));

                    if($entity instanceof Entity) {
                        $entity->spawnToAll();
                    }
                }
            }
            if($success > 0) {
                $this->setDelay(mt_rand($this->getMinSpawnDelay(), $this->getMaxSpawnDelay()));
            }
        } else {
            $this->setDelay($this->getDelay() - 1);
        }
        $this->timings->stopTiming();
        return true;
    }

    public function canUpdate() : bool {
        if(!$this->getLevel()->isChunkLoaded($this->getX() >> 4, $this->getZ() >> 4)) {
            return false;
        }
        if($this->getEntityId() === 0) {
            return false;
        }
        $hasPlayer = false;
        $count = 0;
		
        foreach($this->getLevel()->getEntities() as $entity) {
            if($entity instanceof CorePlayer) {
                if($entity->distance($this->getBlock()) <= 15) {
                    $hasPlayer = true;
                }
            }
            if($entity::NETWORK_ID == $this->getEntityId()) {
                $count++;
            }
        }
        if($hasPlayer and $count < 15) {
            return true;
        }
        return false;
    }

    public function addAdditionalSpawnData(CompoundTag $nbt): void {
        $this->baseData($nbt);
    }

    protected function readSaveData(CompoundTag $nbt): void {
        $this->nbt = $nbt;
    }

    protected function writeSaveData(CompoundTag $nbt): void {
        $this->baseData($nbt);
    }

    public function baseData(CompoundTag $nbt) : void {
        $nbt->setInt("EntityId", $this->getNBT()->getInt("EntityId"));
        $nbt->setInt("Delay", $this->getNBT()->getInt("Delay"));
        $nbt->setInt("SpawnCount", $this->getNBT()->getInt("SpawnCount"));
        $nbt->setInt("SpawnRange", $this->getNBT()->getInt("SpawnRange"));
        $nbt->setInt("MinSpawnDelay", $this->getNBT()->getInt("MinSpawnDelay"));
        $nbt->setInt("MaxSpawnDelay", $this->getNBT()->getInt("MaxSpawnDelay"));
    }
}