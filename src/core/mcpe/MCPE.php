<?php

/**
 * Add-ons to PMMP from TeaSpoon (CortexPE)
 * Add-ons to Entity AI from
 */
declare(strict_types = 1);

namespace core\mcpe;

use core\Core;

use core\utils\Level;

use core\mcpe\inventory\BrewingManager;
use core\mcpe\block\{
	Beacon as BeaconBlock,
    Bed,
    BrewingStand as BrewingStandBlock,
    Cauldron as CauldronBlock,
	DragonEgg,
    EnchantingTable,
    EndPortal,
	EndPortalFrame,
    Hopper as HopperBlock,
    Jukebox as JukeboxBlock,
    LitPumpkin,
    MonsterSpawner,
    Obsidian,
    Portal,
    Pumpkin,
    ShulkerBox as ShulkerBoxBlock,
    SlimeBlock,
	Sponge
};
use core\mcpe\entity\{
	MonsterBase,
	AnimalBase
};
use core\mcpe\entity\monster\flying\EnderDragon;
use core\mcpe\entity\monster\walking\{
	Wither,
	Endermite,
	SnowGolem,
	IronGolem
};
use core\mcpe\entity\monster\swimming\ElderGuardian;
use core\mcpe\entity\object\ItemEntity;
use core\mcpe\item\{
	ArmorStand,
	Bucket,
	Elytra,
	EnchantedBook,
	EndCrystal,
	EyeOfEnder,
	FireCharge,
	Fireworks,
	FishingRod,
	GlassBottle,
	Lead,
	LingeringPotion,
	Minecart,
	Record,
	Trident
};
use core\mcpe\tile\{
    Beacon as BeaconTile,
    BrewingStand as BrewingStandTile,
    Cauldron as CauldronTile,
    Hopper as HopperTile,
    Jukebox as JukeboxTile,
    MobSpawner,
    Shulkerbox as ShulkerboxTile
};
use core\mcpe\level\generator\ender\Ender;
use core\mcpe\network\{
	CraftingDataPacket,
	InventoryTransactionPacket
};

use pocketmine\entity\Entity;

use pocketmine\block\BlockFactory;

use pocketmine\item\{
	ItemFactory,
	SpawnEgg
};

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\tile\Tile;

use pocketmine\math\Vector3;

use pocketmine\utils\Config;

use pocketmine\level\Position;
use pocketmine\level\format\Chunk;
use pocketmine\level\generator\GeneratorManager;

use pocketmine\network\mcpe\protocol\PacketPool;

class MCPE implements Addon {
    private $core;
	/**
	 * @var Config
	 */
    private $cacheFile;

    private $brewingManager, $scoreboardManager;

    public $registeredEntities = [];

	private $runs = 0;
    /** @var int[] */
    public $onPortal = [];

    public static $netherName = "nether";
    /** @var Level */
    public static $netherLevel;

    public static $endName = "ender";
    /** @var Level */
    public static $endLevel;

    public static $loaded = false;
	
    public function __construct(Core $core) {
        $this->core = $core;
		$this->brewingManager = new BrewingManager();

		$this->cacheFile = new Config($core->getDataFolder() . "/mcpe/" . "cache.json", Config::JSON);
		\core\utils\Level::$chunkCounter = $core->getConfig()->getAll();

        BlockFactory::registerBlock(new BeaconBlock(), true);
        BlockFactory::registerBlock(new Bed(), true);
        BlockFactory::registerBlock(new BrewingStandBlock(), true);
        BlockFactory::registerBlock(new CauldronBlock(), true);
        BlockFactory::registerBlock(new DragonEgg(), true);
        BlockFactory::registerBlock(new EnchantingTable(), true);
        BlockFactory::registerBlock(new EndPortal(), true);
		BlockFactory::registerBlock(new EndPortalFrame(), true);
        BlockFactory::registerBlock(new HopperBlock(), true);
        BlockFactory::registerBlock(new JukeboxBlock(), true);
        BlockFactory::registerBlock(new LitPumpkin(), true);
        BlockFactory::registerBlock(new MonsterSpawner(), true);
		BlockFactory::registerBlock(new Obsidian(), true);
        BlockFactory::registerBlock(new Portal(), true);
        BlockFactory::registerBlock(new Pumpkin(), true);
        BlockFactory::registerBlock(new ShulkerBoxBlock(), true);
        BlockFactory::registerBlock(new SlimeBlock(), true);
		BlockFactory::registerBlock(new Sponge(), true);

		foreach(self::ENTITIES as $className => $saveNames) {
			Entity::registerEntity($className, true, $saveNames);

			if(!in_array($className, [
				EnderDragon::class,
				Wither::class,
				ElderGuardian::class,
				Endermite::class,
				ItemEntity::class,
				SnowGolem::class,
				IronGolem::class,
			]) && !in_array($className, self::NON_ENTITIES)) {
				$item = new SpawnEgg(constant($className . "::NETWORK_ID"));

				if(!\pocketmine\item\Item::isCreativeItem($item)) {
					\pocketmine\item\Item::addCreativeItem($item);
				}
			}
			$this->registeredEntities[] = new \ReflectionClass($className);//new $className($core->getServer()->getDefaultLevel(), Entity::createBaseNBT(new Vector3(0, 0, 0), null, lcg_value() * 360, 0));
		}
		ItemFactory::registerItem(new ArmorStand(), true);
		ItemFactory::registerItem(new Bucket(), true);
		ItemFactory::registerItem(new Elytra(), true);
		ItemFactory::registerItem(new EnchantedBook(), true);
		ItemFactory::registerItem(new EndCrystal(), true);
		ItemFactory::registerItem(new EyeOfEnder(), true);
		ItemFactory::registerItem(new FireCharge(), true);
		ItemFactory::registerItem(new Fireworks(), true);
		ItemFactory::registerItem(new FishingRod(), true);
		ItemFactory::registerItem(new GlassBottle(), true);
		ItemFactory::registerItem(new Lead(), true);
		ItemFactory::registerItem(new LingeringPotion(), true);
		ItemFactory::registerItem(new Minecart(), true);
		ItemFactory::registerItem(new Record(\pocketmine\item\Item::RECORD_13, 0, "Music Disc 13"), true);
		ItemFactory::registerItem(new Record(\pocketmine\item\Item::RECORD_CAT, 0, "Music Disc cat"), true);
		ItemFactory::registerItem(new Record(\pocketmine\item\Item::RECORD_BLOCKS, 0, "Music Disc blocks"), true);
		ItemFactory::registerItem(new Record(\pocketmine\item\Item::RECORD_CHIRP, 0, "Music Disc chirp"), true);
		ItemFactory::registerItem(new Record(\pocketmine\item\Item::RECORD_FAR, 0, "Music Disc far"), true);
		ItemFactory::registerItem(new Record(\pocketmine\item\Item::RECORD_MALL, 0, "Music Disc mall"), true);
		ItemFactory::registerItem(new Record(\pocketmine\item\Item::RECORD_MELLOHI, 0, "Music Disc mellohi"), true);
		ItemFactory::registerItem(new Record(\pocketmine\item\Item::RECORD_STAL, 0, "Music Disc stal"), true);
		ItemFactory::registerItem(new Record(\pocketmine\item\Item::RECORD_STRAD, 0, "Music Disc strad"), true);
		ItemFactory::registerItem(new Record(\pocketmine\item\Item::RECORD_WARD, 0, "Music Disc ward"), true);
		ItemFactory::registerItem(new Record(\pocketmine\item\Item::RECORD_11, 0, "Music Disc 11"), true);
		ItemFactory::registerItem(new Record(\pocketmine\item\Item::RECORD_WAIT, 0, "Music Disc wait"), true);
		ItemFactory::registerItem(new Trident(), true);
		\pocketmine\item\Item::initCreativeItems();

		PacketPool::registerPacket(new CraftingDataPacket());
		PacketPool::registerPacket(new InventoryTransactionPacket());

        Tile::registerTile(BeaconTile::class);
        Tile::registerTile(BrewingStandTile::class);
        Tile::registerTile(CauldronTile::class);
        Tile::registerTile(HopperTile::class);
        Tile::registerTile(JukeboxTile::class);
        Tile::registerTile(MobSpawner::class);
        Tile::registerTile(ShulkerboxTile::class);

        $core->getServer()->getPluginManager()->registerEvents(new MCPEListener($core), $core);

        if(!self::$loaded) {
        	self::$loaded = true;
			GeneratorManager::addGenerator(Ender::class, "ender");
		}

        if(!$core->getServer()->loadLevel(self::$netherName)) {
            $core->getServer()->generateLevel(self::$netherName, time(), GeneratorManager::getGenerator("nether"));
        }
        self::$netherLevel = $core->getServer()->getLevelByName(self::$netherName);

        if(!$core->getServer()->loadLevel(self::$endName)){
            $core->getServer()->generateLevel(self::$endName, time(), GeneratorManager::getGenerator("ender"));
        }
        self::$endLevel = $core->getServer()->getLevelByName(self::$endName);

		$properties = new Config($core->getServer()->getDataPath() . "server.properties", Config::PROPERTIES, [
			"motd" => \pocketmine\NAME . " Server",
			"server-port" => 19132,
			"white-list" => false,
			"announce-player-achievements" => true,
			"spawn-protection" => 16,
			"max-players" => 20,
			"spawn-animals" => false,
			"spawn-mobs" => false,
			"gamemode" => 0,
			"force-gamemode" => false,
			"hardcore" => false,
			"pvp" => true,
			"difficulty" => 1,
			"generator-settings" => "",
			"level-name" => "world",
			"level-seed" => "",
			"level-type" => "DEFAULT",
			"enable-query" => true,
			"enable-rcon" => false,
			"rcon.password" => substr(base64_encode(\random_bytes(20)), 3, 10),
			"auto-save" => true,
			"view-distance" => 8,
			"xbox-auth" => true,
			"language" => "eng"
		]);
		if(!$properties->exists("spawn-animals")) {
			$properties->set("spawn-animals", false);
		}
		if(!$properties->exists("spawn-mobs")) {
			$properties->set("spawn-mobs", false);
		}
		if($properties->hasChanged()) {
			$properties->save();
		}
    }

    public function getBrewingManager() : BrewingManager {
    	return $this->brewingManager;
	}

	public function getCacheFile() : Config {
		return $this->cacheFile;
	}
    /**
     * @return Entity[]
     */
    public function getRegisteredEntities() : array {
        return $this->registeredEntities;
    }
	
	public function tick() : void {
    	$this->runs++;
		
		if($this->runs % 1 === 0) {
			if($this->core->getServer()->getConfigBool("spawn-mobs", false)) {
				foreach($this->core->getServer()->getLevels() as $level) {
					if($level->getDifficulty() < Level::DIFFICULTY_EASY) {
						continue;
					}
					$disabled = [];

					foreach($this->core->getWorld()->getAreas() as $area) {
						if(!$area->entitySpawn()) {
							$disabled[] = $area;
						}
					}
					if(in_array($level->getFolderName(), $disabled) or in_array($level->getName(), $disabled)) {
						continue;
					}
					$entities = 0;

					foreach($level->getEntities() as $entity) {
						if($entity instanceof MonsterBase) {
							$entities += 1;
						}
						if($entities >= 200) {
							continue 2;
						}
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
					foreach($chunks as $chunk) {
						$packCenter = new Vector3(mt_rand($chunk->getX() << 4, (($chunk->getX() << 4) + 15)), mt_rand(0, $level->getWorldHeight() - 1), mt_rand($chunk->getZ() << 4, (($chunk->getZ() << 4) + 15)));
						$biomeId = $level->getBiomeId($packCenter->x, $packCenter->z);
						$entityList = self::BIOME_HOSTILE_MOBS[$biomeId] ?? [];

						if(empty($entityList)) {
							continue;
						}
						$entityId = $entityList[array_rand($entityList)];

						if(!$level->getBlockAt($packCenter->x, $packCenter->y, $packCenter->z)->isSolid()) {
							for($attempts = 0, $currentPackSize = 0; $attempts <= 12 and $currentPackSize < 4; $attempts++) {
								$x = mt_rand(-20, 20) + $packCenter->x;
								$z = mt_rand(-20, 20) + $packCenter->z;

								foreach(self::getRegisteredEntities() as $class => $arr) {
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
			if($this->core->getServer()->getConfigBool("spawn-mobs", false) or $this->core->getServer()->getConfigBool("spawn-animals", false)) {
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
                    		//TODO: Check Age
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
		if($this->runs % 20 === 0 && $this->core->getServer()->getConfigBool("spawn-mobs", false)) {
            foreach($this->core->getServer()->getLevels() as $level) {
				$disabled = [];

				foreach($this->core->getWorld()->getAreas() as $area) {
					if(!$area->entitySpawn()) {
						$disabled[] = $area;
					}
				}
				if(in_array($level->getFolderName(), $disabled) or in_array($level->getName(), $disabled)) {
					continue;
				}
				$entities = 0;

				foreach($level->getEntities() as $entity) {
					if($entity instanceof AnimalBase) {
						$entities += 1;
					}
					if($entities >= 200) {
						continue 2;
					}
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
				foreach($chunks as $chunk) {
					$packCenter = new Vector3(mt_rand($chunk->getX() << 4, (($chunk->getX() << 4) + 15)), mt_rand(0, $level->getWorldHeight() - 1), mt_rand($chunk->getZ() << 4, (($chunk->getZ() << 4) + 15)));
					$biomeId = $level->getBiomeId($packCenter->x, $packCenter->z);
					$entityList = self::BIOME_ANIMALS[$biomeId] ?? [];

					if(empty($entityList)) {
						continue;
					}
					$entityId = $entityList[array_rand($entityList)];

					if(!$level->getBlockAt($packCenter->x, $packCenter->y, $packCenter->z)->isSolid()) {
						for($attempts = 0, $currentPackSize = 0; $attempts <= 12 and $currentPackSize < 4; $attempts++) {
							$x = mt_rand(-20, 20) + $packCenter->x;
							$z = mt_rand(-20, 20) + $packCenter->z;

							foreach($this->getRegisteredEntities() as $class => $arr) {
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
            foreach($this->core->getServer()->getOnlinePlayers() as $player) {
				$level = $player->level;
				$chunk = $player->chunk;

				if($level !== null and $chunk !== null) {
					$hash = Level::chunkHash($chunk->getX(), $chunk->getZ());

					if(!isset(Level::$chunkCounter[$hash])) {
						Level::$chunkCounter[$hash . ":" . $level->getFolderName()] = 0;
					}
					Level::$chunkCounter[$hash . ":" . $level->getFolderName()] += 1;
				}
            }
		}
	}
}
