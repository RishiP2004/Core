<?php

namespace core\mcpe;

use core\mcpe\entity\animal\flying\{
    Bat,
    Parrot
};

use core\mcpe\entity\animal\jumping\{
    Rabbit
};

use core\mcpe\entity\animal\swimming\{
    Squid
};

use core\mcpe\entity\animal\walking\{
    Chicken,
    Cow,
    Donkey,
    Horse,
    Llama,
    Mooshroom,
    Mule,
    Ocelot,
    Pig,
    Sheep,
    SkeletonHorse,
    Villager
};

use core\mcpe\entity\monster\flying\{
    Blaze,
    EnderDragon,
    Ghast,
    Vex
};

use core\mcpe\entity\monster\jumping\{
    MagmaCube,
    Slime
};

use core\mcpe\entity\monster\swimming\{
    ElderGuardian,
    Guardian
};

use core\mcpe\entity\monster\walking\{
    CaveSpider,
    Creeper,
    Enderman,
    Endermite,
    Evoker,
    Husk,
    IronGolem,
    PolarBear,
    Shulker,
    Silverfish,
    Skeleton,
    SnowGolem,
    Spider,
    Stray,
    Vindicator,
    Witch,
    Wither,
    WitherSkeleton,
    Wolf,
    Zombie,
    ZombiePigman,
    ZombieVillager
};

use pocketmine\level\biome\Biome;

interface Addon {
    const SPAWN_COUNT = 4;
    const SPAWN_RANGE = 4;
    const MIN_SPAWN_DELAY = 200;
    const MAX_SPAWN_DELAY = 800;

    const DROPS = true;

    const TIER_COSTS = [
        2 => 50000,
        3 => 100000,
        4 => 300000
    ];
    const TYPES = [
        10 => "chicken",
        11 => "cow",
        12 => "pig",
        13 => "sheep",
        14 => "wolf",
        15 => "villager",
        16 => "mooshroom",
        17 => "squid",
        18 => "rabbit",
        19 => "bat",
        20 => "iron_golem",
        21 => "snow_golem",
        22 => "ocelot",
        23 => "horse",
        24 => "donkey",
        25 => "mule",
        26 => "skeleton_horse",
        27 => "zombie_horse",
        28 => "polar_bear",
        29 => "llama",
        30 => "parrot",
        32 => "zombie",
        33 => "creeper",
        34 => "skeleton",
        35 => "spider",
        36 => "zombie_pigman",
        37 => "slime",
        38 => "enderman",
        39 => "silverfish",
        40 => "cave_spider",
        41 => "ghast",
        42 => "magma_cube",
        43 => "blaze",
        44 => "zombie_villager",
        45 => "witch",
        46 => "stray",
        47 => "husk",
        48 => "wither_skeleton",
        49 => "guardian",
        50 => "elder_guardian",
        51 => "npc",
        52 => "wither",
        53 => "ender_dragon",
        54 => "shulker",
        55 => "endermite",
        56 => "agent",
        57 => "vindicator",
        61 => "armor_stand",
        62 => "tripod_camera",
        63 => "player",
        64 => "item",
        65 => "tnt",
        66 => "falling_block",
        67 => "moving_block",
        68 => "xp_bottle",
        69 => "xp_orb",
        70 => "eye_of_ender_signal",
        71 => "endercrystal",
        72 => "fireworks_rocket",
        76 => "shulker_bullet",
        77 => "fishing_hook",
        78 => "chalkboard",
        79 => "dragon_fireball",
        80 => "arrow",
        81 => "snowball",
        82 => "egg",
        83 => "painting",
        84 => "minecart",
        85 => "large_fireball",
        86 => "splash_potion",
        87 => "ender_pearl",
        88 => "leash_knot",
        89 => "wither_skull",
        90 => "boat",
        91 => "wither_skull_dangerous",
        93 => "lightning_bolt",
        94 => "small_fireball",
        95 => "area_effect_cloud",
        96 => "hopper_minecart",
        97 => "tnt_minecart",
        98 => "chest_minecart",
        100 => "command_block_minecart",
        101 => "lingering_potion",
        102 => "llama_spit",
        103 => "evocation_fang",
        104 => "evocation_illager",
        105 => "vex"
    ];
    const ENTITIES = [
        "Bat",
        "Parrot",
        "Rabbit",
        "Squid",
        "Chicken",
        "Cow",
        "Donkey",
        "Horse",
        "Llama",
        "Mooshroom",
        "Mule",
        "Ocelot",
        "Pig",
        "Rabbit",
        "Sheep",
        "Skeleton Horse",
        "Villager",
        "Blaze",
        "Ender Dragon",
        "Ghast",
        "Vex",
        "Magma Cube",
        "Slime",
        "Elder Guardian",
        "Guardian",
        "Cave Spider",
        "Creeper",
        "Enderman",
        "Endermite",
        "Evoker",
        "Husk",
        "Iron Golem",
        "Polar Bear",
        "Shulker",
        "Silverfish",
        "Skeleton",
        "Snow Golem",
        "Spider",
        "Stray",
        "Vindicator",
        "Witch",
        "Wither",
        "Wither Skeleton",
        "Wolf",
        "Zombie",
        "Zombie Pigman",
        "Zombie Villager"
    ];
    const ENTITY_CLASSES = [
        Bat::class,
        Parrot::class,
        Rabbit::class,
        Squid::class,
        Chicken::class,
        Cow::class,
        Donkey::class,
        Horse::class,
        Llama::class,
        Mooshroom::class,
        Mule::class,
        Ocelot::class,
        Pig::class,
        Sheep::class,
        SkeletonHorse::class,
        Villager::class,
        Blaze::class,
        EnderDragon::class,
        Ghast::class,
        Vex::class,
        MagmaCube::class,
        Slime::class,
        ElderGuardian::class,
        Guardian::class,
        CaveSpider::class,
        Creeper::class,
        Enderman::class,
        Endermite::class,
        Evoker::class,
        Husk::class,
        IronGolem::class,
        PolarBear::class,
        Shulker::class,
        Silverfish::class,
        Skeleton::class,
        SnowGolem::class,
        Spider::class,
        Stray::class,
        Vindicator::class,
        Witch::class,
        Wither::class,
        WitherSkeleton::class,
        Wolf::class,
        Zombie::class,
        ZombiePigman::class,
        ZombieVillager::class
    ];
    public const BIOME_ENTITIES = [
		Biome::OCEAN => [
			Squid::NETWORK_ID
			// TODO: water mobs
		],
		Biome::PLAINS => [
			Zombie::NETWORK_ID,
			Skeleton::NETWORK_ID,
			Creeper::NETWORK_ID,
			Spider::NETWORK_ID,
			Witch::NETWORK_ID
		],
		Biome::DESERT => [
			Zombie::NETWORK_ID,
			Skeleton::NETWORK_ID,
			Creeper::NETWORK_ID,
			Spider::NETWORK_ID,
			Witch::NETWORK_ID
		],
		Biome::MOUNTAINS => [
			Zombie::NETWORK_ID,
			Skeleton::NETWORK_ID,
			Creeper::NETWORK_ID,
			Spider::NETWORK_ID,
			Witch::NETWORK_ID
		],
		Biome::FOREST => [
			Zombie::NETWORK_ID,
			Skeleton::NETWORK_ID,
			Creeper::NETWORK_ID,
			Spider::NETWORK_ID,
			Witch::NETWORK_ID
		],
		Biome::TAIGA => [
			Zombie::NETWORK_ID,
			Skeleton::NETWORK_ID,
			Creeper::NETWORK_ID,
			Spider::NETWORK_ID,
			Witch::NETWORK_ID
		],
		Biome::SWAMP => [
			Zombie::NETWORK_ID,
			Skeleton::NETWORK_ID,
			Creeper::NETWORK_ID,
			Spider::NETWORK_ID,
			Witch::NETWORK_ID
		],
		Biome::RIVER => [
			Squid::NETWORK_ID
			// TODO: water mobs
		],
		Biome::HELL => [
			ZombiePigman::NETWORK_ID,
			Ghast::NETWORK_ID,
			MagmaCube::NETWORK_ID
		],
		Biome::ICE_PLAINS => [
			Zombie::NETWORK_ID,
			Stray::NETWORK_ID,
			Creeper::NETWORK_ID,
			Spider::NETWORK_ID,
			Witch::NETWORK_ID
		],
		Biome::SMALL_MOUNTAINS => [
			Zombie::NETWORK_ID,
			Skeleton::NETWORK_ID,
			Creeper::NETWORK_ID,
			Spider::NETWORK_ID,
			Witch::NETWORK_ID
		],
		Biome::BIRCH_FOREST => [
			Zombie::NETWORK_ID,
			Skeleton::NETWORK_ID,
			Creeper::NETWORK_ID,
			Spider::NETWORK_ID,
			Witch::NETWORK_ID
		]
	];
	public const BIOME_HOSTILE_MOBS = [
		Biome::OCEAN => [
			Squid::NETWORK_ID // Temporary to fix empty array messages
			// TODO: water mobs
		],
		Biome::PLAINS => [
			Zombie::NETWORK_ID,
			Skeleton::NETWORK_ID,
			Creeper::NETWORK_ID,
			Spider::NETWORK_ID,
			Witch::NETWORK_ID
		],
		Biome::DESERT => [
			Zombie::NETWORK_ID,
			Skeleton::NETWORK_ID,
			Creeper::NETWORK_ID,
			Spider::NETWORK_ID,
			Witch::NETWORK_ID
		],
		Biome::MOUNTAINS => [
			Zombie::NETWORK_ID,
			Skeleton::NETWORK_ID,
			Creeper::NETWORK_ID,
			Spider::NETWORK_ID,
			Witch::NETWORK_ID
		],
		Biome::FOREST => [
			Zombie::NETWORK_ID,
			Skeleton::NETWORK_ID,
			Creeper::NETWORK_ID,
			Spider::NETWORK_ID,
			Witch::NETWORK_ID
		],
		Biome::TAIGA => [
			Zombie::NETWORK_ID,
			Skeleton::NETWORK_ID,
			Creeper::NETWORK_ID,
			Spider::NETWORK_ID,
			Witch::NETWORK_ID
		],
		Biome::SWAMP => [
			Zombie::NETWORK_ID,
			Skeleton::NETWORK_ID,
			Creeper::NETWORK_ID,
			Spider::NETWORK_ID,
			Slime::NETWORK_ID,
			Witch::NETWORK_ID
		],
		Biome::RIVER => [
			Squid::NETWORK_ID // Temporary to fix empty array messages
			// TODO: water mobs
		],
		Biome::HELL => [
			ZombiePigman::NETWORK_ID,
			Ghast::NETWORK_ID,
			MagmaCube::NETWORK_ID
		],
		Biome::ICE_PLAINS => [
			Zombie::NETWORK_ID,
			Skeleton::NETWORK_ID,
			Creeper::NETWORK_ID,
			Spider::NETWORK_ID,
			Witch::NETWORK_ID
		],
		Biome::SMALL_MOUNTAINS => [
			Zombie::NETWORK_ID,
			Skeleton::NETWORK_ID,
			Creeper::NETWORK_ID,
			Spider::NETWORK_ID,
			Witch::NETWORK_ID
		],
		Biome::BIRCH_FOREST => [
			Zombie::NETWORK_ID,
			Skeleton::NETWORK_ID,
			Creeper::NETWORK_ID,
			Spider::NETWORK_ID,
			Witch::NETWORK_ID
		]
	];
	public const BIOME_ANIMALS = [
		Biome::OCEAN => [
			Squid::NETWORK_ID,
			Dolphin::NETWORK_ID
			// TODO: water mobs
		],
		Biome::PLAINS => [
			Cow::NETWORK_ID,
			Pig::NETWORK_ID,
			Sheep::NETWORK_ID,
			Chicken::NETWORK_ID,
			Horse::NETWORK_ID,
			Donkey::NETWORK_ID,
			Rabbit::NETWORK_ID
		],
		Biome::DESERT => [
			Cow::NETWORK_ID,
			Pig::NETWORK_ID,
			Sheep::NETWORK_ID,
			Chicken::NETWORK_ID
		],
		Biome::MOUNTAINS => [
			Cow::NETWORK_ID,
			Pig::NETWORK_ID,
			Sheep::NETWORK_ID,
			Chicken::NETWORK_ID,
			Llama::NETWORK_ID
		],
		Biome::FOREST => [
			Cow::NETWORK_ID,
			Pig::NETWORK_ID,
			Sheep::NETWORK_ID,
			Chicken::NETWORK_ID
		],
		Biome::TAIGA => [
			Cow::NETWORK_ID,
			Pig::NETWORK_ID,
			Sheep::NETWORK_ID,
			Chicken::NETWORK_ID
		],
		Biome::SWAMP => [
			Cow::NETWORK_ID,
			Pig::NETWORK_ID,
			Sheep::NETWORK_ID,
			Chicken::NETWORK_ID
		],
		Biome::RIVER => [
			Squid::NETWORK_ID
			// TODO: water mobs
		],
		Biome::HELL => [
			// none spawn
		],
		Biome::ICE_PLAINS => [
			Cow::NETWORK_ID,
			Pig::NETWORK_ID,
			Sheep::NETWORK_ID,
			Chicken::NETWORK_ID,
			PolarBear::NETWORK_ID
		],
		Biome::SMALL_MOUNTAINS => [
			Cow::NETWORK_ID,
			Pig::NETWORK_ID,
			Sheep::NETWORK_ID,
			Chicken::NETWORK_ID
		],
		Biome::BIRCH_FOREST => [
			Cow::NETWORK_ID,
			Pig::NETWORK_ID,
			Sheep::NETWORK_ID,
			Chicken::NETWORK_ID
		]
	];
}
