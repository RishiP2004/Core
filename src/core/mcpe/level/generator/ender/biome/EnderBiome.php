<?php

declare(strict_types = 1);

namespace core\mcpe\level\generator\ender\biome;

use pocketmine\level\biome\Biome;

class EnderBiome extends Biome {
	public function getName() : string {
		return "Ender";
	}
}