<?php

declare(strict_types = 1);

namespace core\utils;

use core\mcpe\block\{
    Portal,
    EndPortal
};

use pocketmine\entity\{
	Effect,
	EffectInstance
};

use pocketmine\item\{
    Item,
	Potion
};

use pocketmine\block\Block;

use pocketmine\level\Position;
use pocketmine\level\particle\{
    AngryVillagerParticle,
    BubbleParticle,
    CriticalParticle,
    DustParticle,
    EnchantmentTableParticle,
    EnchantParticle,
    ExplodeParticle,
    FlameParticle,
    HappyVillagerParticle,
    HeartParticle,
    HugeExplodeParticle,
    InkParticle,
    InstantEnchantParticle,
	EntityFlameParticle,
    ItemBreakParticle,
    LavaDripParticle,
    LavaParticle,
    Particle,
    PortalParticle,
    RainSplashParticle,
    RedstoneParticle,
    SmokeParticle,
    SplashParticle,
    SporeParticle,
    TerrainParticle,
    WaterDripParticle,
    WaterParticle,
	DestroyBlockParticle
};
use pocketmine\network\mcpe\protocol\{
	ActorEventPacket,
	AddActorPacket
};

use pocketmine\utils\Color;

class Entity extends \pocketmine\entity\Entity {
    const USABLES = [
        Item::DISPENSER,
        Item::NOTEBLOCK,
        Item::CHEST,
        Item::CRAFTING_TABLE,
        Item::FURNACE,
        Item::BURNING_FURNACE,
        Item::STANDING_SIGN,
        Item::OAK_DOOR,
        Item::WALL_SIGN,
        Item::LEVER,
        Item::IRON_DOOR,
        Item::STONE_BUTTON,
        Item::CAKE_BLOCK,
        93, //OFF REDSTONE REPEATER BLOCK
        94, //ON REDSTONE REPEATER BLOCK
        Item::WOODEN_TRAPDOOR,
        Item::ENCHANTING_TABLE,
        Item::BREWING_STAND_BLOCK,
        Item::CAULDRON_BLOCK,
        Item::ENDER_CHEST,
        135, //UNKNOWN
        Item::BEACON,
        Item::ANVIL,
        Item::TRAPPED_CHEST,
        149, //OFF REDSTONE COMPARATOR
        150, //ON REDSTONE COMPARATOR
        Item::HOPPER_BLOCK,
        Item::SPRUCE_FENCE_GATE,
        Item::BIRCH_FENCE_GATE,
        Item::JUNGLE_FENCE_GATE,
        Item::DARK_OAK_FENCE_GATE,
        Item::ACACIA_FENCE_GATE,
        Item::SPRUCE_DOOR_BLOCK,
        Item::BIRCH_DOOR_BLOCK,
        Item::JUNGLE_DOOR_BLOCK,
        Item::ACACIA_DOOR_BLOCK,
        Item::DARK_OAK_DOOR_BLOCK
    ];
    const CONSUMABLES = [
        Item::POTION,
        Item::GLASS_BOTTLE,
        Item::DRAGON_BREATH,
        Item::SPLASH_POTION,
        Item::ELYTRA,
        Item::APPLE,
        Item::ENCHANTED_GOLDEN_APPLE,
        Item::GOLDEN_APPLE,
        Item::STEAK,
        Item::CAKE,
        Item::BEEF,
        Item::CHICKEN,
        Item::POTATO,
        Item::CARROT,
        Item::FISH,
        Item::RABBIT,
        Item::PORKCHOP,
        Item::MUTTON_COOKED
    ];
    const OTHER = [
        Item::BUCKET,
        Item::FLINT_AND_STEEL,
        Item::WOODEN_SHOVEL,
        Item::WOODEN_HOE,
        Item::STONE_SHOVEL,
        Item::STONE_HOE,
        Item::GOLDEN_SHOVEL,
        Item::GOLDEN_HOE,
        Item::IRON_SHOVEL,
        Item::IRON_HOE,
        Item::DIAMOND_SHOVEL,
        Item::DIAMOND_HOE,
    ];

    const NON_SOLID_BLOCKS = [
        Block::SAPLING,
        Block::WATER,
        Block::STILL_WATER,
        Block::LAVA,
        Block::STILL_LAVA,
        Block::COBWEB,
        Block::TALL_GRASS,
        Block::DEAD_BUSH,
        Block::DANDELION,
        Block::POPPY,
        Block::BROWN_MUSHROOM,
        Block::RED_MUSHROOM,
        Block::TORCH,
        Block::FIRE,
        Block::WHEAT_BLOCK,
        Block::SIGN_POST,
        Block::WALL_SIGN,
        Block::SUGARCANE_BLOCK,
        Block::PUMPKIN_STEM,
        Block::MELON_STEM,
        Block::VINE,
        Block::CARROT_BLOCK,
        Block::POTATO_BLOCK,
        Block::DOUBLE_PLANT
    ];

    public static function parseEffect(string $name = "INVALID", int $seconds = 60, int $amplifier = 1) {
        $effect = Effect::getEffectByName($name);
        
        if($effect !== null) {
            return new EffectInstance($effect, $seconds * 20, $amplifier);
        }
        return null;
    }
    
    public static function skinFromImage(string $path) : string {
        $img = imagecreatefrompng($path);
        [$k, $l] = getimagesize($path);
        $bytes = '';

        for($y = 0; $y < $l; ++$y) {
            for($x = 0; $x < $k; ++$x) {
                $argb = imagecolorat($img, $x, $y);
                $bytes .= chr(($argb >> 16) & 0xff).\chr(($argb >> 8) & 0xff).\chr($argb & 0xff).\chr((~($argb >> 24) << 1) & 0xff);
            }
        }
        imagedestroy($img);
        return $bytes;
    }

    public static function getCubes(array $geometryData) : array {
        $cubes = [];

        foreach($geometryData["bones"] as $bone) {
            if(!isset($bone["cubes"])) {
                continue;
            }
            if($bone["mirror"] ?? false) {
                throw new \InvalidArgumentException('Unsupported geometry data');
            }
            foreach($bone["cubes"] as $cubeData) {
                $cube = [];
                $cube["x"] = $cubeData["size"][0];
                $cube["y"] = $cubeData["size"][1];
                $cube["z"] = $cubeData["size"][2];
                $cube["uvX"] = $cubeData["uv"][0];
                $cube["uvY"] = $cubeData["uv"][1];
                $cubes[] = $cube;
            }
        }
        return $cubes;
    }

    public static function getSkinBounds(array $cubes, float $scale = 1.0) : array {
        $bounds = [];

        foreach($cubes as $cube) {
            $x = (int) ($scale * $cube["x"]);
            $y = (int) ($scale * $cube["y"]);
            $z = (int) ($scale * $cube["z"]);
            $uvX = (int) ($scale * $cube["uvX"]);
            $uvY = (int) ($scale * $cube["uvY"]);
            $bounds[] = [
                "min" => [
                    "x" => $uvX + $z,
                    "y" => $uvY
                ], 'max' => [
                    "x" => $uvX + $z + (2 * $x) - 1,
                    "y" => $uvY + $z - 1
                ]
            ];
            $bounds[] = [
                "min" => [
                    "x" => $uvX,
                    "y" => $uvY + $z
                ], 'max' => [
                    "x" => $uvX + (2 * ($z + $x)) - 1,
                    "y" => $uvY + $z + $y - 1
                ]
            ];
        }
        return $bounds;
    }

    public static function fakeDeath(\pocketmine\entity\Entity $entity) {
        $level = $entity->getLevel();
        $chunkX = $entity->x >> 4;
        $chunkZ = $entity->z >> 4;
        $pk = new AddActorPacket();
        $pk->type = $entity::NETWORK_ID;
        $pk->position = $entity->asVector3();
        $pk->entityRuntimeId = $entityId = Entity::$entityCount++;

        $pk->metadata[Entity::DATA_BOUNDING_BOX_WIDTH] = [Entity::DATA_TYPE_FLOAT, 0];
        $pk->metadata[Entity::DATA_BOUNDING_BOX_HEIGHT] = [Entity::DATA_TYPE_FLOAT, 0];
        $level->addChunkPacket($chunkX, $chunkZ, $pk);

        $pk2 = new ActorEventPacket();
        $pk2->entityRuntimeId = $entityId;
        $pk2->event = ActorEventPacket::DEATH_ANIMATION;

        $level->addChunkPacket($chunkX, $chunkZ, $pk2);
    }

    public static function isInsideOfPortal(Entity $entity) : bool {
		if($entity->level === null) {
			return false;
		}
        $block = $entity->getLevel()->getBlock($entity->floor());

        if($block instanceof Portal) {
            return true;
        }
        return false;
    }

    public static function isInsideOfEndPortal(Entity $entity) : bool {
		if($entity->level === null) {
			return false;
		}
        $block = $entity->getLevel()->getBlock($entity);

        if($block instanceof EndPortal) {
            return true;
        }
        return false;
    }

    public static function checkSnowGolemStructure(Block $head) : array {
        $level = $head->getLevel();
        $block1 = ($level->getBlock($head->subtract(0, 1, 0))->getId() === Block::SNOW_BLOCK);
        $block2 = ($level->getBlock($head->subtract(0, 2, 0))->getId() === Block::SNOW_BLOCK);

        return [
            ($block1 && $block2),
            "Y"
        ];
    }

    public static function checkIronGolemStructure(Block $head) : array {
        $level = $head->getLevel();
        $block1 = ($level->getBlock($head->subtract(0, 1, 0))->getId() == Block::IRON_BLOCK);
        $block2 = ($level->getBlock($head->subtract(0, 2, 0))->getId() == Block::IRON_BLOCK);
        $block3 = $level->getBlock($head->subtract(1, 1, 0));
        $block4 = $level->getBlock($head->add(1, -1, 0));
        $block5 = $level->getBlock($head->subtract(0, 1, 1));
        $block6 = $level->getBlock($head->add(0, -1, 1));

        if($block1 && $block2) {
            if($block3->getId() == Block::IRON_BLOCK && $block4->getId() == Block::IRON_BLOCK) {
                return [
                    true,
                    "X"
                ];
            }
            if($block5->getId() == Block::IRON_BLOCK && $block6->getId() == Block::IRON_BLOCK) {
                return [
                    true,
                     "Z"
                ];
            }
        }
        return [
            false,
            "NULL"
        ];
    }

    public static function getParticle(string $name, Position $position, ?int $data = null) : ?Particle {
		switch(strtolower($name)) {
            case "explode":
                return new ExplodeParticle($position);
            case "huge explode":
                return new HugeExplodeParticle($position);
            case "bubble":
                return new BubbleParticle($position);
            case "splash":
                return new SplashParticle($position);
            case "water":
                return new WaterParticle($position);
            case "critical":
                return new CriticalParticle($position);
            case "spell":
                return new EnchantParticle($position);
            case "instant spell":
                return new InstantEnchantParticle($position);
            case "smoke":
                return new SmokeParticle($position, ($data === null ? 0 : $data));
            case "drip water":
                return new WaterDripParticle($position);
            case "drip lava":
                return new LavaDripParticle($position);
            case "spore":
                return new SporeParticle($position);
            case "portal":
                return new PortalParticle($position);
            case "entity flame":
                return new EntityFlameParticle($position);
            case "flame":
                return new FlameParticle($position);
            case "lava":
                return new LavaParticle($position);
            case "redstone":
                return new RedstoneParticle($position, ($data === null ? 1 : $data));
            case "snowball":
                return new ItemBreakParticle($position, Item::get(Item::SNOWBALL));
            case "slime":
                return new ItemBreakParticle($position, Item::get(Item::SLIMEBALL));
            case "heart":
                return new HeartParticle($position, ($data === null ? 0 : $data));
            case "ink":
                return new InkParticle($position, ($data === null ? 0 : $data));
            case "enchantment table":
                return new EnchantmentTableParticle($position);
            case "happy villager":
                return new HappyVillagerParticle($position);
            case "angry villager":
                return new AngryVillagerParticle($position);
            case "rain":
                return new RainSplashParticle($position);
            case "colourful":
            	return new DustParticle ($position, rand(0, 255), rand(0, 255), rand(0, 255));
        }
        if(substr($name, 0, 5) === "item_") {
            $array = explode("_", $name);
            return new ItemBreakParticle($position, new Item($array[1]));
        }
        if(substr($name, 0, 6) === "block_") {
            $array = explode("_", $name);
            return new TerrainParticle($position, Block::get($array[1]));
        }
        if(substr($name, 0, 9) === "destroyblock_") {
            $array = explode("_", $name);
            return new DestroyBlockParticle($position, Block::get($array[1]));
        }
		if(substr($name, 0, 5 ) === "dust_") {
			$arr = explode("_", $name);

			if(strpos($arr[1], ",") !== false) {
				$rgb = explode(",", $arr[1]);

				if(is_numeric($rgb[0]) && is_numeric($rgb[1]) && is_numeric($rgb[2])) {
					if($rgb[0] > -1 && $rgb[0] < 256 && $rgb[1] > -1 && $rgb[1] < 256 && $rgb[2] > -1 && $rgb[2] < 256) {
						return new DustParticle($position, $rgb[0], $rgb[1], $rgb[2]);
					}
				}
			}
			switch($arr[1]) {
				case "red":
				case "4":
				case "c":
					return new DustParticle($position, 252, 8, 8);
				case "orange" :
				case "6" :
					return new DustParticle($position, 252, 195, 8);
				case "yellow" :
				case "e" :
					return new DustParticle($position, 252, 252, 8);
				case "green":
				case "a" :
				case "2" :
					return new DustParticle($position, 8, 252, 8);
				case "aqua" :
				case "b" :
					return new DustParticle($position, 8, 252, 228);
				case "blue" :
				case "1" :
					return new DustParticle($position, 8, 8, 252);
				case "purple" :
				case "d" :
				case "5" :
					return new DustParticle($position, 252, 8, 252);
				case "pink" :
					return new DustParticle($position, 252, 8, 150);
				case "white" :
				case "f" :
					return new DustParticle($position, 255, 255, 255);
				case "black" :
				case "0" :
					return new DustParticle($position, 0, 0, 0);
				case "grey" :
				case "gray" :
					return new DustParticle($position, 138, 138, 138);
				default :
					return new DustParticle($position, 255, 255, 255);
			}
		}
		return new TerrainParticle($position, Block::get(0));
    }

	public static function getPotionColor(int $effectID) : Color {
		return Potion::getPotionEffectsById($effectID)[0]->getColor();
	}
}