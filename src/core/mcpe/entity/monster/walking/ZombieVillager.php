<?php

namespace core\mcpe\entity\monster\walking;

class ZombieVillager extends Zombie {
	public const NETWORK_ID = self::ZOMBIE_VILLAGER;

	public $width = 1.031, $height = 2.125;

	public function getName() : string {
		return "Zombie Villager";
	}
}