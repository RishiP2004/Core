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
    ArmorStand,
    EndCrystal,
	Item
};

use core\mcpe\entity\projectile\FishingHook;

use core\mcpe\entity\vehicle\{
    Boat,
    BrokenMinecart,
    Minecart
};

use pocketmine\level\biome\Biome;

interface Addon {
    const DROPS = true;

    const TIER_COSTS = [
        2 => 50000,
        3 => 100000,
        4 => 300000
    ];
	const ENTITIES = [
		Chicken::class => ['Chicken', 'minecraft:chicken'],
		Cow::class => ['Cow', 'minecraft:cow'],
		Pig::class => ['Pig', 'minecraft:pig'],
		Sheep::class => ['sheep', 'minecraft:sheep'],
		Wolf::class => ['Wolf', 'minecraft:wolf'],
		Villager::class => ['Villager', 'minecraft:villager'],
		Mooshroom::class => ['Mooshroom', 'minecraft:mooshroom'],
		Squid::class => ['Squid', 'minecraft:squid'],
		Rabbit::class => ['Rabbit', 'minecraft:rabbit'],
		Bat::class => ['Bat', 'minecraft:bat'],
		IronGolem::class => ['IronGolem', 'minecraft:irongolem'],
		SnowGolem::class => ['SnowGolem', 'minecraft:snowgolem'],
		Ocelot::class => ['Ocelot', 'minecraft:ocelot'],
		Horse::class => ['Horse', 'minecraft:horse'],
		Donkey::class => ['Donkey', 'minecraft:donkey'],
		Mule::class => ['Mule', 'minecraft:mule'],
		SkeletonHorse::class => ['SkeletonHorse', 'minecraft:skeletonhorse'],
		ZombieHorse::class => ['ZombieHorse', 'minecraft:zombiehorse'],
		PolarBear::class => ['PolarBear', 'minecraft:polarbear'],
		Llama::class => ['Llama', 'minecraft:llama'],
		Parrot::class => ['Parrot', 'minecraft:parrot'],
		Dolphin::class => ['Dolphin', 'minecraft:dolphin'],
		Zombie::class => ['Zombie', 'minecraft:zombie'],
		Creeper::class => ['Creeper', 'minecraft:creeper'],
		Skeleton::class => ['Skeleton', 'minecraft:skeleton'],
		Spider::class => ['Spider', 'minecraft:spider'],
		ZombiePigman::class => ['PigZombie', 'minecraft:pigzombie'],
		Slime::class => ['Slime', 'minecraft:slime'],
		Enderman::class => ['Enderman', 'minecraft:enderman'],
		Silverfish::class => ['Silverfish', 'minecraft:silverfish'],
		CaveSpider::class => ['CaveSpider', 'minecraft:cavespider'],
		Ghast::class => ['Ghast', 'minecraft:ghast'],
		MagmaCube::class => ['MagmaCube', 'minecraft:magmacube'],
		Blaze::class => ['Blaze', 'minecraft:blaze'],
		ZombieVillager::class => ['ZombieVillager', 'minecraft:zombievillager'],
		Witch::class => ['Witch', 'minecraft:witch'],
		Stray::class => ['Stray', 'minecraft:stray'],
		Husk::class => ['Husk', 'minecraft:husk'],
		WitherSkeleton::class => ['WitherSkeleton', 'minecraft:witherskeleton'],
		Guardian::class => ['Guardian', 'minecraft:guardian'],
		ElderGuardian::class => ['ElderGuardian', 'minecraft:elderguardian'],
		Wither::class => ['Wither', 'minecraft:wither'],
		EnderDragon::class => ['EnderDragon', 'minecraft:enderdragon'],
		Shulker::class => ['Shulker', 'minecraft:shulker'],
		Endermite::class => ['Endermite', 'minecraft:endermite'],
		Vindicator::class => ['Vindicator', 'minecraft:vindicator'],
		//ArmorStand::class => [],
		//TripodCamera::class => [],
		// player
		Item::class => ['Item', 'minecraft:item'],
		//TNT::class => [],
		//FallingBlock::class => [],
		//MovingBlock::class => [],
		//ExperienceBottle::class => [],
		//ExperienceOrb::class => [],
		//EyeOfEnder::class => [],
		//EnderCrystal::class => ['EnderCrystal', 'minecraft:ender_crystal'],
		//FireworksRocket::class => ['FireworksRocket',	'minecraft:fireworks_rocket'],
		//Trident::class => ['Thrown Trident', 'minecraft:thrown_trident'],
		//
		//ShulkerBullet::class => [],
		//FishingHook::class => ['FishingHook', 'minecraft:fishinghook'],
		//chalkboard
		//DragonFireball::class => [],
		//Arrow::class => [],
		//Snowball::class => [],
		//Egg::class => [],
		//Painting::class => [],
		//Minecart::class => ['Minecart', 'minecraft:minecart'],
		//LargeFireball::class => [],
		//SplashPotion::class => [],
		//EnderPearl::class => [],
		//LeashKnot::class => [],
		//WitherSkull::class => [],
		//Boat::class => [],
		//DangerousWitherSkull::class => [],
		//Lightning::class => [],
		//Fireball::class => [],
		//AreaEffectCloud::class => [],
		//HopperMinecart::class => [],
		//TNTMinecart::class => [],
		//ChestMinecart::class => [],
		//
		//CommandBlockMinecart::class => [],
		//LingeringPotion::class => [],
		//LlamaSpit::class => [],
		//EvocationFang::class => [],
		//Evoker::class => [],
		//Vex::class => [],
		//ice bomb
		//balloon
		//pufferfish
		//salmon
		//drowned
		//tropical fish
		//fish
	];
	public const BIOME_ENTITIES = [
		Biome::OCEAN => [
		    Dolphin::NETWORK_ID,
			Squid::NETWORK_ID
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
			Squid::NETWORK_ID
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
			Squid::NETWORK_ID
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
}
