<?php

declare(strict_types = 1);

namespace core\mcpe;

use core\mcpe\entity\animal\flying\{
    Bat,
    Parrot
};
use core\mcpe\entity\animal\jumping\{
    Rabbit
};
use core\mcpe\entity\animal\swimming\{
    Dolphin,
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
	Drowned,
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
	ZombieHorse,
    ZombiePigman,
    ZombieVillager
};
use core\mcpe\entity\object\{
	AreaEffectCloud,
	ArmorStand,
	EnderCrystal,
	EyeOfEnder,
	ItemEntity,
	LeadKnot,
	Lightning,
	TripodCamera
};
use core\mcpe\entity\projectile\{
	Arrow,
	Egg,
	Firework,
	FishingHook,
	LingeringPotion,
	Trident,
	WitherSkull
};
use core\mcpe\entity\vehicle\{
	Boat,
	ChestMinecart,
	CommandBlockMinecart,
	HopperMinecart,
	Minecart,
	TNTMinecart
};

use pocketmine\level\biome\Biome;

interface Addon {
    const TIER_COSTS = [
        2 => 50000,
        3 => 100000,
        4 => 300000
    ];
    const ENTITY_SPAWN = false;
    const ENTITY_DESPAWN = true;

	const ENTITIES = [
		Bat::class => ['Bat', 'minecraft:bat'],
		Parrot::class => ['Parrot', 'minecraft:parrot'],
		Rabbit::class => ['Rabbit', 'minecraft:rabbit'],
		Dolphin::class => ['Dolphin', 'minecraft:dolphin'],
		Squid::class => ['Squid', 'minecraft:squid'],
		Chicken::class => ['Chicken', 'minecraft:chicken'],
		Cow::class => ['Cow', 'minecraft:cow'],
		Donkey::class => ['Donkey', 'minecraft:donkey'],
		Horse::class => ['Horse', 'minecraft:horse'],
		Llama::class => ['Llama', 'minecraft:llama'],
		Mooshroom::class => ['Mooshroom', 'minecraft:mooshroom'],
		Mule::class => ['Mule', 'minecraft:mule'],
		Ocelot::class => ['Ocelot', 'minecraft:ocelot'],
		Pig::class => ['Pig', 'minecraft:pig'],
		Sheep::class => ['Sheep', 'minecraft:sheep'],
		SkeletonHorse::class => ['SkeletonHorse', 'minecraft:skeletonhorse'],
		Villager::class => ['Villager', 'minecraft:villager'],
		Blaze::class => ['Blaze', 'minecraft:blaze'],
		EnderDragon::class => ['EnderDragon', 'minecraft:enderdragon'],
		Ghast::class => ['Ghast', 'minecraft:ghast'],
		Vex::class => ['Vex', 'minecraft:vex'],
		MagmaCube::class => ['MagmaCube', 'minecraft:magmacube'],
		Slime::class => ['Slime', 'minecraft:slime'],
		Drowned::class => ['Drowned', 'minecraft:drowned'],
		ElderGuardian::class => ['ElderGuardian', 'minecraft:elderguardian'],
		Guardian::class => ['Guardian', 'minecraft:guardian'],
		CaveSpider::class => ['CaveSpider', 'minecraft:cavespider'],
		Creeper::class => ['Creeper', 'minecraft:creeper'],
		Enderman::class => ['Enderman', 'minecraft:enderman'],
		Endermite::class => ['Endermite', 'minecraft:endermite'],
		Evoker::class => ['Evoker', 'minecraft:evoker'],
		Husk::class => ['Husk', 'minecraft:husk'],
		IronGolem::class => ['IronGolem', 'minecraft:irongolem'],
		PolarBear::class => ['PolarBear', 'minecraft:polarbear'],
		Shulker::class => ['Shulker', 'minecraft:shulker'],
		Silverfish::class => ['Silverfish', 'minecraft:silverfish'],
		Skeleton::class => ['Skeleton', 'minecraft:skeleton'],
		Snowgolem::class => ['Snowgolem', 'minecraft:snowgolem'],
		Spider::class => ['Spider', 'minecraft:spider'],
		Stray::class => ['Stray', 'minecraft:stray'],
		Vindicator::class => ['Vindicator', 'minecraft:vindicator'],
		Witch::class => ['Witch', 'minecraft:witch'],
		Wither::class => ['Wither', 'minecraft:wither'],
		WitherSkeleton::class => ['WitherSkeleton', 'minecraft:witherskeleton'],
		Wolf::class => ['Wolf', 'minecraft:wolf'],
		Zombie::class => ['Zombie', 'minecraft:zombie'],
		ZombieHorse::class => ['ZombieHorse', 'minecraft:zombiehorse'],
		ZombiePigman::class => ['ZombiePigman', 'minecraft:zombiepigman'],
		ZombieVillager::class => ['ZombieVillager', 'minecraft:zombievillager'],
		AreaEffectCloud::class => ['AreaEffectCloud', 'minecraft:areaeffectcloud'],
		ArmorStand::class => ['ArmorStand', 'minecraft:armor_stand'],
		EnderCrystal::class => ['EnderCrystal', 'minecraft:ender_crystal'],
		EyeOfEnder::class => ['EyeOfEnder', 'minecraft:eyeofender'],
		ItemEntity::class => ['Item', 'minecraft:item'],
		LeadKnot::class => ['LeadKnot', 'minecraft:leadknot'],
		Lightning::class => ['Lightning', 'minecraft:lightning'],
		TripodCamera::class => ['TripodCamera', 'minecraft:tripodcamera'],
		Arrow::class => ['Arrow', 'minecraft:arrow'],
		Egg::class => ['Egg', 'minecraft:egg'],
		Firework::class => ['Firework', 'minecraft:firework'],
		FishingHook::class => ['FishingHook', 'minecraft:fishinghook'],
		LingeringPotion::class => ['LingeringPotion', 'minecraft:lingeringpotion'],
		Trident::class => ['Trident', 'minecraft:trident'],
		WitherSkull::class => ['WitherSkull', 'minecraft:wither_skull'],
		Boat::class => ['Boat', 'minecraft:boat'],
		ChestMinecart::class => ['ChestMinecart', 'minecraft:chestminecart'],
		CommandBlockMinecart::class => ['CommandBlockMinecart', 'minecraft:commandblockminecart'],
		HopperMinecart::class => ['HopperMinecart', 'minecraft:hopperminecart'],
		Minecart::class => ['Minecart', 'minecraft:minecart'],
		TNTMinecart::class => ['TNTMinecart', 'minecraft:tntminecart']
	];
	const NON_ENTITIES = [
		AreaEffectCloud::class,
		ArmorStand::class,
		EnderCrystal::class,
		EyeOfEnder::class,
		ItemEntity::class,
		LeadKnot::class,
		Lightning::class,
		TripodCamera::class,
		Arrow::class,
		Egg::class,
		Firework::class,
		FishingHook::class,
		LingeringPotion::class,
		Trident::class,
		WitherSkull::class,
		Boat::class,
		ChestMinecart::class,
		CommandBlockMinecart::class,
		HopperMinecart::class,
		Minecart::class,
		TNTMinecart::class
	];

	public const BIOME_ENTITIES = [
		Biome::OCEAN => [
		    Dolphin::NETWORK_ID,
			Squid::NETWORK_ID,
			Drowned::NETWORK_ID
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
			Squid::NETWORK_ID,
			Drowned::NETWORK_ID
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
			Squid::NETWORK_ID,
			Drowned::NETWORK_ID
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
			Squid::NETWORK_ID,
			Drowned::NETWORK_ID
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
            Dolphin::NETWORK_ID,
			Squid::NETWORK_ID
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
		],
		Biome::HELL => [
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

	public const BANE_OF_ARTHROPODS_AFFECTED_ENTITIES = [
		"Spider", "Cave Spider",
		"Silverfish", "Endermite"
	];

	public const
		END = 9,
		FROZEN_OCEAN = 10,
		FROZEN_RIVER = 11,
		ICE_MOUNTAINS = 13,
		MUSHROOM_ISLAND = 14,
		MUSHROOM_ISLAND_SHORE = 15,
		BEACH = 16,
		DESERT_HILLS = 17,
		FOREST_HILLS = 18,
		TAIGA_HILLS = 19,
		BIRCH_FOREST_HILLS = 28,
		ROOFED_FOREST = 29,
		COLD_TAIGA = 30,
		COLD_TAIGA_HILLS = 31,
		MEGA_TAIGA = 32,
		MEGA_TAIGA_HILLS = 33,
		EXTREME_HILLS_PLUS = 34,
		SAVANNA = 35,
		SAVANNA_PLATEAU = 36,
		MESA = 37,
		MESA_PLATEAU_F = 38,
		MESA_PLATEAU = 39,
		VOID = 127;

	public const WHITE = 0;
	public const ORANGE = 1;
	public const MAGENTA = 2;
	public const LIGHT_BLUE = 3;
	public const YELLOW = 4;
	public const LIME = 5;
	public const PINK = 6;
	public const GRAY = 7;
	public const LIGHT_GRAY = 8;
	public const CYAN = 9;
	public const PURPLE = 10;
	public const BLUE = 11;
	public const BROWN = 12;
	public const GREEN = 13;
	public const RED = 14;
	public const BLACK = 15;

	public CONST META_TO_NAMES = [
		self::WHITE => "White",
		self::ORANGE => "Orange",
		self::MAGENTA => "Magenta",
		self::LIGHT_BLUE => "Light Blue",
		self::YELLOW => "Yellow",
		self::LIME => "Lime",
		self::PINK => "Pink",
		self::GRAY => "Gray",
		self::LIGHT_GRAY => "Light Gray",
		self::CYAN => "Cyan",
		self::PURPLE => "Purple",
		self::BLUE => "Blue",
		self::BROWN => "Brown",
		self::GREEN => "Green",
		self::RED => "Red",
		self::BLACK => "Black"
	];

	public CONST NAMES_TO_META = [
		"White" => self::WHITE,
		"Orange" => self::ORANGE,
		"Magenta" => self::MAGENTA,
		"Light Blue" => self::LIGHT_BLUE,
		"Yellow" => self::YELLOW,
		"Lime" => self::LIME,
		"Pink" => self::PINK,
		"Gray" => self::GRAY,
		"Light Gray" => self::LIGHT_GRAY,
		"Cyan" => self::CYAN,
		"Purple" => self::PURPLE,
		"Blue" => self::BLUE,
		"Brown" => self::BROWN,
		"Green" => self::GREEN,
		"Red" => self::RED,
		"Black" => self::BLACK
	];
}
