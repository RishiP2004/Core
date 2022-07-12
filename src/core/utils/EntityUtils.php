<?php

declare(strict_types = 1);

namespace core\utils;

use pocketmine\item\{
	Item,
	ItemIds,
	Potion,
	StringToItemParser,
	VanillaItems};

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\entity\Entity;
use pocketmine\world\Position;

use pocketmine\world\particle\{
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
};

use pocketmine\network\mcpe\protocol\{
	ActorEventPacket,
	AddActorPacket
};

use pocketmine\color\Color;

final class EntityUtils {
    const USABLES = [
        ItemIds::DISPENSER,
		ItemIds::NOTEBLOCK,
		ItemIds::CHEST,
		ItemIds::CRAFTING_TABLE,
		ItemIds::FURNACE,
		ItemIds::BURNING_FURNACE,
		ItemIds::STANDING_SIGN,
		ItemIds::OAK_DOOR,
		ItemIds::WALL_SIGN,
		ItemIds::LEVER,
		ItemIds::IRON_DOOR,
		ItemIds::STONE_BUTTON,
		ItemIds::CAKE_BLOCK,
        93, //OFF REDSTONE REPEATER BLOCK
        94, //ON REDSTONE REPEATER BLOCK
		ItemIds::WOODEN_TRAPDOOR,
		ItemIds::ENCHANTING_TABLE,
		ItemIds::BREWING_STAND_BLOCK,
		ItemIds::CAULDRON_BLOCK,
		ItemIds::ENDER_CHEST,
        135, //UNKNOWN
		ItemIds::BEACON,
		ItemIds::ANVIL,
		ItemIds::TRAPPED_CHEST,
        149, //OFF REDSTONE COMPARATOR
        150, //ON REDSTONE COMPARATOR
		ItemIds::HOPPER_BLOCK,
		ItemIds::SPRUCE_FENCE_GATE,
		ItemIds::BIRCH_FENCE_GATE,
		ItemIds::JUNGLE_FENCE_GATE,
		ItemIds::DARK_OAK_FENCE_GATE,
		ItemIds::ACACIA_FENCE_GATE,
		ItemIds::SPRUCE_DOOR_BLOCK,
		ItemIds::BIRCH_DOOR_BLOCK,
		ItemIds::JUNGLE_DOOR_BLOCK,
		ItemIds::ACACIA_DOOR_BLOCK,
		ItemIds::DARK_OAK_DOOR_BLOCK
    ];
    const CONSUMABLES = [
		ItemIds::POTION,
		ItemIds::GLASS_BOTTLE,
		ItemIds::DRAGON_BREATH,
		ItemIds::SPLASH_POTION,
		ItemIds::ELYTRA,
		ItemIds::APPLE,
		ItemIds::ENCHANTED_GOLDEN_APPLE,
		ItemIds::GOLDEN_APPLE,
		ItemIds::STEAK,
		ItemIds::CAKE,
		ItemIds::BEEF,
		ItemIds::CHICKEN,
		ItemIds::POTATO,
		ItemIds::CARROT,
		ItemIds::FISH,
		ItemIds::RABBIT,
		ItemIds::PORKCHOP,
		ItemIds::MUTTON_COOKED
    ];
    const OTHER = [
		ItemIds::BUCKET,
		ItemIds::FLINT_AND_STEEL,
		ItemIds::WOODEN_SHOVEL,
		ItemIds::WOODEN_HOE,
		ItemIds::STONE_SHOVEL,
		ItemIds::STONE_HOE,
		ItemIds::GOLDEN_SHOVEL,
		ItemIds::GOLDEN_HOE,
		ItemIds::IRON_SHOVEL,
		ItemIds::IRON_HOE,
		ItemIds::DIAMOND_SHOVEL,
		ItemIds::DIAMOND_HOE,
    ];

    const NON_SOLID_BLOCKS = [
        BlockLegacyIds::SAPLING,
		BlockLegacyIds::WATER,
		BlockLegacyIds::STILL_WATER,
		BlockLegacyIds::LAVA,
		BlockLegacyIds::STILL_LAVA,
		BlockLegacyIds::COBWEB,
		BlockLegacyIds::TALL_GRASS,
		BlockLegacyIds::DEAD_BUSH,
		BlockLegacyIds::DANDELION,
		BlockLegacyIds::POPPY,
		BlockLegacyIds::BROWN_MUSHROOM,
		BlockLegacyIds::RED_MUSHROOM,
		BlockLegacyIds::TORCH,
		BlockLegacyIds::FIRE,
		BlockLegacyIds::WHEAT_BLOCK,
		BlockLegacyIds::SIGN_POST,
		BlockLegacyIds::WALL_SIGN,
		BlockLegacyIds::SUGARCANE_BLOCK,
		BlockLegacyIds::PUMPKIN_STEM,
		BlockLegacyIds::MELON_STEM,
		BlockLegacyIds::VINE,
		BlockLegacyIds::CARROT_BLOCK,
		BlockLegacyIds::POTATO_BLOCK,
		BlockLegacyIds::DOUBLE_PLANT
    ];

	public static function skinFromImage(string $path) : string {
		$bytes = "";
		if(!file_exists($path)){
			return $bytes;
		}
		$img = imagecreatefrompng($path);

		[$width, $height] = getimagesize($path);

		for($y = 0; $y < $height; ++$y) {
			for($x = 0; $x < $width; ++$x){
				$argb = imagecolorat($img, $x, $y);
				$bytes .= chr(($argb >> 16) & 0xff) . chr(($argb >> 8) & 0xff) . chr($argb & 0xff) . chr((~($argb >> 24) << 1) & 0xff);
			}
		}
		imagedestroy($img);
		return $bytes;
	}

    public static function fakeDeath(Entity $entity) {
        $level = $entity->getWorld();
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

    public static function checkSnowGolemStructure(Block $head) : array {
        $level = $head->getPosition()->getWorld();
        $block1 = ($level->getBlock($head->subtract(0, 1, 0))->getId() === BlockLegacyIds::SNOW_BLOCK);
        $block2 = ($level->getBlock($head->subtract(0, 2, 0))->getId() === BlockLegacyIds::SNOW_BLOCK);

        return [
            ($block1 && $block2),
            "Y"
        ];
    }

    public static function checkIronGolemStructure(Block $head) : array {
        $level = $head->getPosition()->getWorld();
        $block1 = ($level->getBlock($head->subtract(0, 1, 0))->getId() == BlockLegacyIds::IRON_BLOCK);
        $block2 = ($level->getBlock($head->subtract(0, 2, 0))->getId() == BlockLegacyIds::IRON_BLOCK);
        $block3 = $level->getBlock($head->subtract(1, 1, 0));
        $block4 = $level->getBlock($head->add(1, -1, 0));
        $block5 = $level->getBlock($head->subtract(0, 1, 1));
        $block6 = $level->getBlock($head->add(0, -1, 1));

        if($block1 && $block2) {
            if($block3->getId() == BlockLegacyIds::IRON_BLOCK && $block4->getId() == BlockLegacyIds::IRON_BLOCK) {
                return [
                    true,
                    "X"
                ];
            }
            if($block5->getId() == BlockLegacyIds::IRON_BLOCK && $block6->getId() == BlockLegacyIds::IRON_BLOCK) {
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
                return new ExplodeParticle();
            case "huge explode":
                return new HugeExplodeParticle();
            case "bubble":
                return new BubbleParticle();
            case "splash":
                return new SplashParticle();
            case "water":
                return new WaterParticle();
            case "critical":
                return new CriticalParticle();
            case "spell":
                return new EnchantParticle();
            case "instant spell":
                return new InstantEnchantParticle();
            case "smoke":
                return new SmokeParticle(($data === null ? 0 : $data));
            case "drip water":
                return new WaterDripParticle();
            case "drip lava":
                return new LavaDripParticle();
            case "spore":
                return new SporeParticle();
            case "portal":
                return new PortalParticle();
            case "entity flame":
                return new EntityFlameParticle();
            case "flame":
                return new FlameParticle();
            case "lava":
                return new LavaParticle();
            case "redstone":
                return new RedstoneParticle(($data === null ? 1 : $data));
            case "snowball":
                return new ItemBreakParticle(VanillaItems::SNOWBALL());
            case "slime":
                return new ItemBreakParticle(VanillaItems::SLIMEBALL());
            case "heart":
                return new HeartParticle(($data === null ? 0 : $data));
            case "ink":
                return new InkParticle(($data === null ? 0 : $data));
            case "enchantment table":
                return new EnchantmentTableParticle();
            case "happy villager":
                return new HappyVillagerParticle();
            case "angry villager":
                return new AngryVillagerParticle();
            case "rain":
                return new RainSplashParticle();
            case "colourful":
            	return new DustParticle(new Color(rand(0, 255), rand(0, 255), rand(0, 255));
        }
        if(str_starts_with($name, "item_")) {
            $array = explode("_", $name);
            return new ItemBreakParticle(StringToItemParser::getInstance()->parse($array[1]));
        }
        if(substr($name, 0, 6) === "block_") {
            $array = explode("_", $name);
			//todo
            return new TerrainParticle(BlockFactory::getInstance()->get($array[1]));
        }
        if(substr($name, 0, 9) === "destroyblock_") {
            $array = explode("_", $name);
            return new DestroyBlockParticle(Block::get($array[1]));
        }
		if(substr($name, 0, 5 ) === "dust_") {
			$arr = explode("_", $name);

			if(strpos($arr[1], ",") !== false) {
				$rgb = explode(",", $arr[1]);

				if(is_numeric($rgb[0]) && is_numeric($rgb[1]) && is_numeric($rgb[2])) {
					if($rgb[0] > -1 && $rgb[0] < 256 && $rgb[1] > -1 && $rgb[1] < 256 && $rgb[2] > -1 && $rgb[2] < 256) {
						return new DustParticle(new Color($rgb[0], $rgb[1], $rgb[2]));
					}
				}
			}
			switch($arr[1]) {
				case "red":
				case "4":
				case "c":
					return new DustParticle(252, 8, 8);
				case "orange" :
				case "6" :
					return new DustParticle( 252, 195, 8);
				case "yellow" :
				case "e" :
					return new DustParticle( 252, 252, 8);
				case "green":
				case "a" :
				case "2" :
					return new DustParticle( 8, 252, 8);
				case "aqua" :
				case "b" :
					return new DustParticle( 8, 252, 228);
				case "blue" :
				case "1" :
					return new DustParticle(8, 8, 252);
				case "purple" :
				case "d" :
				case "5" :
					return new DustParticle(252, 8, 252);
				case "pink" :
					return new DustParticle( 252, 8, 150);
				case "white" :
				case "f" :
					return new DustParticle( 255, 255, 255);
				case "black" :
				case "0" :
					return new DustParticle( 0, 0, 0);
				case "grey" :
				case "gray" :
					return new DustParticle( 138, 138, 138);
				default :
					return new DustParticle( 255, 255, 255);
			}
		}
		return new TerrainParticle(VanillaBlocks::AIR());
    }

	public static function getPotionColor(int $effectID) : Color {
		return PotionTypeIdMap::getInstance()->fromId($effectID)[0]->getColor();
	}
}