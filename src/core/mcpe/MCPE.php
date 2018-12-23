<?php

namespace core\mcpe;

use core\Core;

use core\mcpe\block\{
    Beacon as BeaconBlock,
    Hopper as HopperBlock,
    MonsterSpawner
};

use core\mcpe\entity\{
    MonsterBase,
    AnimalBase
};

use core\mcpe\tile\{
    Beacon as BeaconTile,
    Hopper as HopperTile,
    MobSpawner
};

use pocketmine\entity\Entity;

use pocketmine\block\BlockFactory;

use pocketmine\tile\Tile;

use pocketmine\level\{
    Level,
    Position
};

use pocketmine\level\format\Chunk;

use pocketmine\math\Vector3;

class MCPE implements Addon {
    private $core;

    public $registeredEntities = [];

	private $runs = 0;
	
    public function __construct(Core $core) {
        $this->core = $core;

        foreach(self::ENTITY_CLASSES as $className) {
            Entity::registerEntity($className, true);

            $this->registeredEntities[] = $className;
        }
        //register rest
        BlockFactory::registerBlock(new BeaconBlock(), true);
        BlockFactory::registerBlock(new HopperBlock(), true);
        BlockFactory::registerBlock(new MonsterSpawner(), true);
        Tile::registerTile(BeaconTile::class);
        Tile::registerTile(HopperTile::class);
        Tile::registerTile(MobSpawner::class);
    }

    public function getSpawnCount() : int {
        return self::SPAWN_COUNT;
    }

    public function getSpawnRange() : int {
        return self::SPAWN_RANGE;
    }

    public function getMinSpawnDelay() : int {
        return self::MIN_SPAWN_DELAY;
    }

    public function getMaxSpawnDelay() : int {
        return self::MAX_SPAWN_DELAY;
    }

    public function drops() : bool {
        return self::DROPS;
    }

    public function getTierCost(int $tier) : array {
        return self::TIER_COSTS[$tier];
    }
	
	public function tick() {
		$this->runs++;
		
		if($this->runs % 1 === 0) {
			if($this->core->getServer()->getConfigBool("spawn-mobs", true)) {
                foreach($this->core->getServer()->getLevels() as $level) {
                    if($level->getDifficulty() < Level::DIFFICULTY_EASY) {
                        continue;
                    }
                    /** @var Chunk[] $chunks */
                    $chunks = [];

                    foreach($level->getPlayers() as $player) {
                        foreach($player->usedChunks as $hash => $sent) {
                            if($sent) {
                                Level::getXZ($hash, $chunkX, $chunkZ);
                                $chunks[$hash] = $player->getLevel()->getChunk($chunkX, $chunkZ, true);
                            }
                        }
                    }
                    $entities = 0;

                    foreach($chunks as $chunk) {
                        foreach($chunk->getEntities() as $entity) {
                            if($entity instanceof MonsterBase) {
                                $entities += 1;
                            }
                            if($entities >= 200) {
                                return;
                            }
                        }
                    }
                    foreach($chunks as $chunk) {
                        $packCenter = new Vector3(mt_rand($chunk->getX() << 4, (($chunk->getX() << 4) + 15)), mt_rand(0, $level->getWorldHeight() - 1), mt_rand($chunk->getZ() << 4, (($chunk->getZ() << 4) + 15)));
                        $biomeId = $level->getBiomeId($packCenter->x, $packCenter->z);
                        $entityList = self::BIOME_HOSTILE_MOBS[$biomeId];
						
						if(empty($entityList)) {
							continue;
						}
						$entityId = $entityList[array_rand(self::BIOME_HOSTILE_MOBS[$biomeId])];
						
                        if(!$level->getBlockAt($packCenter->x, $packCenter->y, $packCenter->z)->isSolid()) {
                            for($attempts = 0, $currentPackSize = 0; $attempts <= 12 and $currentPackSize < 4; $attempts++) {
                                $x = mt_rand(-20, 20) + $packCenter->x;
                                $z = mt_rand(-20, 20) + $packCenter->z;

                                foreach($this->registeredEntities as $class) {
                                    if($class instanceof MonsterBase and $class::NETWORK_ID === $entityId) {
                                        $entity = $class::spawnMob(new Position($x + 0.5, $packCenter->y, $z + 0.5, $level));

                                        if($entity !== null) {
                                            $currentPackSize++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
			}
			if($this->core->getServer()->getConfigBool("spawn-mobs", true) or $this->core->getServer()->getConfigBool("spawn-animals", true)) {
                foreach($this->core->getServer()->getLevels() as $level) {
                    /** @var Chunk[] $chunks */
                    $chunks = [];
                    foreach($level->getPlayers() as $player) {
                        foreach($player->usedChunks as $hash => $sent) {
                            if($sent) {
                                Level::getXZ($hash, $chunkX, $chunkZ);
                                $chunks[$hash] = $player->getLevel()->getChunk($chunkX, $chunkZ, true);
                            }
                        }
                    }
                    foreach($chunks as $chunk) {
                        if(mt_rand(1, 50) !== 1) {
                            continue;
                        }
                        foreach($chunk->getEntities() as $entity) {
                            $distanceCheck = true;

                            foreach($entity->getViewers() as $player) {
                                if($entity->distance($player) < 54) {
                                    $distanceCheck = false;
                                    break;
                                }
                            }
                            // TODO: check age
                            if($entity instanceof MonsterBase and $distanceCheck and $entity->getLevel()->getFullLight($entity->floor()) > 8 and !$entity->isPersistent()) {
                                $entity->flagForDespawn();
                            } else if($entity instanceof AnimalBase and $distanceCheck and $entity->getLevel()->getFullLight($entity->floor()) < 7 and !$entity->isPersistent()) {
                                $entity->flagForDespawn();
                            }
                        }
                    }
                }
			}
		}
		if($this->runs % 20 === 0 && $this->core->getServer()->getConfigBool("spawn-mobs", true)) {
            foreach($this->core->getServer()->getLevels() as $level) {
                /** @var Chunk[] $chunks */
                $chunks = [];

                foreach($level->getPlayers() as $player) {
                    foreach($player->usedChunks as $hash => $sent) {
                        if($sent) {
                            Level::getXZ($hash, $chunkX, $chunkZ);
                            $chunks[$hash] = $player->getLevel()->getChunk($chunkX, $chunkZ, true);
                        }
                    }
                }
                $entities = 0;

                foreach($chunks as $chunk) {
                    foreach($chunk->getEntities() as $entity) {
                        if($entity instanceof AnimalBase) {
                            $entities += 1;
                        }
                        if($entities >= 200) {
                            return;
                        }
                    }
                }
                foreach($chunks as $chunk) {
                    $packCenter = new Vector3(mt_rand($chunk->getX() << 4, (($chunk->getX() << 4) + 15)), mt_rand(0, $level->getWorldHeight() - 1), mt_rand($chunk->getZ() << 4, (($chunk->getZ() << 4) + 15)));
                    $biomeId = $level->getBiomeId($packCenter->x, $packCenter->z);
                    $entityList = self::BIOME_ANIMALS[$biomeId];
						
					if(empty($entityList)) {
						continue;
					}
					$entityId = $entityList[array_rand(self::BIOME_ANIMALS[$biomeId])];
						
                    if(!$level->getBlockAt($packCenter->x, $packCenter->y, $packCenter->z)->isSolid()) {
                        for($attempts = 0, $currentPackSize = 0; $attempts <= 12 and $currentPackSize < 4; $attempts++) {
                            $x = mt_rand(-20, 20) + $packCenter->x;
                            $z = mt_rand(-20, 20) + $packCenter->z;
                            foreach($this->registeredEntities as $class) {
                                if($class instanceof AnimalBase and $class::NETWORK_ID === $entityId) {
                                    $entity = $class::spawnMob(new Position($x + 0.5, $packCenter->y, $z + 0.5, $level));

                                    if($entity !== null) {
                                        $currentPackSize++;
                                    }
                                }
                            }
                        }
                    }
                }
            }
		}
		if($this->runs % 20 * 60 * 60) {
            foreach($this->core->getServer()->getLevels() as $level) {
                foreach($level->getPlayers() as $player) {
                    $chunk = $player->chunk;
                    if($chunk !== null) {
                        if(!isset($chunk->inhabitedTime)) {
							$chunk->inhabitedTime = 1;
						} else {
							$chunk->inhabitedTime += 1;
						}
                    }
                }
            }
		}
	}
}
