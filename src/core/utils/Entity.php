<?php

namespace core\utils;

use pocketmine\entity\{
    Effect,
    Human
};

use pocketmine\item\{
    Item,
    ItemFactory
};

use pocketmine\entity\EffectInstance;

use pocketmine\network\mcpe\protocol\{
    AddEntityPacket,
    EntityEventPacket
};

use pocketmine\block\{
    Block,
    BlockFactory
};

use pocketmine\level\particle\{
    AngryVillagerParticle,
    BlockForceFieldParticle,
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
    HugeExplodeSeedParticle,
    InkParticle,
    InstantEnchantParticle,
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
    WaterParticle
};

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
    
    public static function skinFromImage($image) : string {
        $combine = [];
       
        for($y = 0; $y < imagesy($image); $y++) {
            for($x = 0; $x < imagesx($image); $x++) {
                $color = imagecolorsforindex($image, imagecolorat($image, $x, $y));
                $color["alpha"] = (($color["alpha"] << 1) ^ 0xff) - 1;
                $combine[] = sprintf("%02x%02x%02x%02x", $color["red"], $color["green"], $color["blue"], $color["alpha"] ?? 0);
            }
        }
        $data = hex2bin(implode("", $combine));
        return $data;
    }

    public static function getCubes(array $geometryData): array{
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
        $pk = new AddEntityPacket();
        $pk->type = $entity::NETWORK_ID;
        $pk->position = $entity->asVector3();
        $pk->entityRuntimeId = $entityId = Entity::$entityCount++;

        $pk->metadata[Entity::DATA_BOUNDING_BOX_WIDTH] = [Entity::DATA_TYPE_FLOAT, 0];
        $pk->metadata[Entity::DATA_BOUNDING_BOX_HEIGHT] = [Entity::DATA_TYPE_FLOAT, 0];
        $level->addChunkPacket($chunkX, $chunkZ, $pk);

        $pk2 = new EntityEventPacket();
        $pk2->entityRuntimeId = $entityId;
        $pk2->event = EntityEventPacket::DEATH_ANIMATION;

        $level->addChunkPacket($chunkX, $chunkZ, $pk2);
    }

    public static function getXpDropsForEntity(Entity $e) : int {
        switch($e::NETWORK_ID){
            case Entity::CHICKEN:
            case Entity::COW:
            case Entity::HORSE:
            case Entity::DONKEY:
            case Entity::MULE:
            case Entity::SKELETON_HORSE:
            case Entity::ZOMBIE_HORSE:
            case Entity::MOOSHROOM:
            case Entity::LLAMA:
            case Entity::OCELOT:
            case Entity::PARROT:
            case Entity::PIG:
            case Entity::POLAR_BEAR:
            case Entity::SHEEP:
            case Entity::SQUID:
            case Entity::RABBIT:
            case Entity::WOLF:
                return mt_rand(1, 3);
            case Entity::BAT:
                return 0;
            // golems //
            case Entity::IRON_GOLEM:
            case Entity::SNOW_GOLEM:
                return 0;
            // monsters //
            case Entity::CAVE_SPIDER:
            case Entity::CREEPER:
            case Entity::ENDERMAN:
            case Entity::GHAST:
            case Entity::HUSK:
            case Entity::SHULKER:
            case Entity::SILVERFISH:
            case Entity::SKELETON:
            case Entity::SPIDER:
            case Entity::STRAY:
            case Entity::VINDICATOR:
            case Entity::WITCH:
            case Entity::WITHER_SKELETON:
            case Entity::ZOMBIE:
            case Entity::ZOMBIE_PIGMAN:
                return 5;
            case Entity::ENDERMITE:
            case Entity::VEX:
                return 3;
            case Entity::SLIME:
            case Entity::MAGMA_CUBE:
                return mt_rand(1, 4);
            case Entity::BLAZE:
            case Entity::GUARDIAN:
            case Entity::ELDER_GUARDIAN:
            case Entity::EVOCATION_ILLAGER:
                return 10;
            case Human::NETWORK_ID:
            case Entity::VILLAGER:
                return 0;
            case Entity::ENDER_DRAGON:
                return (boolval(rand(0, 1)) ? 12000 : 500);
            case Entity::WITHER:
                return 50;
            case Entity::LIGHTNING_BOLT:
                return 0;
        }

        return 0;
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

    public static function getParticle(string $name, ?int $data = null) : ?Particle{
        switch($name){
            case "explode":
                return new ExplodeParticle();
            case "hugeexplosion":
                return new HugeExplodeParticle();
            case "hugeexplosionseed":
                return new HugeExplodeSeedParticle();
            case "bubble":
                return new BubbleParticle();
            case "splash":
                return new SplashParticle();
            case "wake":
            case "water":
                return new WaterParticle();
            case "crit":
                return new CriticalParticle();
            case "smoke":
                return new SmokeParticle($data ?? 0);
            case "spell":
                return new EnchantParticle();
            case "instantspell":
                return new InstantEnchantParticle();
            case "dripwater":
                return new WaterDripParticle();
            case "driplava":
                return new LavaDripParticle();
            case "townaura":
            case "spore":
                return new SporeParticle();
            case "portal":
                return new PortalParticle();
            case "flame":
                return new FlameParticle();
            case "lava":
                return new LavaParticle();
            case "reddust":
                return new RedstoneParticle($data ?? 1);
            case "snowballpoof":
                return new ItemBreakParticle(ItemFactory::get(Item::SNOWBALL));
            case "slime":
                return new ItemBreakParticle(ItemFactory::get(Item::SLIMEBALL));
            case "itembreak":
                if($data !== null and $data !== 0) {
                    return new ItemBreakParticle(ItemFactory::get($data));
                }
                break;
            case "terrain":
                if($data !== null and $data !== 0) {
                    return new TerrainParticle(BlockFactory::get($data));
                }
                break;
            case "heart":
                return new HeartParticle($data ?? 0);
            case "ink":
                return new InkParticle($data ?? 0);
            case "droplet":
                return new RainSplashParticle();
            case "enchantmenttable":
                return new EnchantmentTableParticle();
            case "happyvillager":
                return new HappyVillagerParticle();
            case "angryvillager":
                return new AngryVillagerParticle();
            case "forcefield":
                return new BlockForceFieldParticle($data ?? 0);
        }
        if(strpos($name, "iconcrack_") === 0) {
            $d = explode("_", $name);

            if(count($d) === 3) {
                return new ItemBreakParticle(ItemFactory::get((int) $d[1], (int) $d[2]));
            }
        } else if(strpos($name, "blockcrack_") === 0) {
            $d = explode("_", $name);

            if(count($d) === 2) {
                return new TerrainParticle(BlockFactory::get(((int) $d[1]) & 0xff, ((int) $d[1]) >> 12));
            }
        } else if(strpos($name, "blockdust_") === 0) {
            $d = explode("_", $name);

            if(count($d) >= 4) {
                return new DustParticle(((int) $d[1]) & 0xff, ((int) $d[2]) & 0xff, ((int) $d[3]) & 0xff, isset($d[4]) ? ((int) $d[4]) & 0xff : 255);
            }
        }
        return null;
    }
}