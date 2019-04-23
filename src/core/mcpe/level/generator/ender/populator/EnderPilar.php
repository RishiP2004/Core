<?php

declare(strict_types = 1);

namespace core\mcpe\level\generator\ender\populator;

use pocketmine\utils\Random;

use pocketmine\block\Block;

use pocketmine\level\ChunkManager;
use pocketmine\level\generator\populator\Populator;

class EnderPilar extends Populator {
	private const radii = [3, 4, 3, 5, 3, 4, 3, 3, 5, 4, 5, 3, 5, 4, 4, 5, 5, 4, 4, 4, 5];
	/** @var ChunkManager */
	private $level;

	private $randomAmount;

	private $baseAmount;

	public function setRandomAmount($amount) {
		$this->randomAmount = $amount;
	}

	public function setBaseAmount($amount) {
		$this->baseAmount = $amount;
	}

	public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random) {
		if(mt_rand(0, 100) <= 50) {
			$this->level = $level;
			$x = $random->nextRange(0, 15);
			$z = $random->nextRange(0, 15);
			$height = mt_rand(76, 103);
			$radius = self::radii[array_rand(self::radii)];

			for($ny = 0; $ny < $height; $ny++) {
				for($r = ($radius / 10); $r < $radius; $r += ($radius / 10)) {
					$nd = 360 / (2 * pi() * $r);

					for($d = 0; $d < 360; $d += $nd) {
						$level->setBlockIdAt(intval($x + (cos(deg2rad($d)) * $r)), intval($ny), intval($z + (sin(deg2rad($d)) * $r)), Block::OBSIDIAN);
					}
				}
			}
			if(mt_rand(1, 2) === 1) {
				if($radius === 3) {
					$bradius = 1;
				} else {
					$bradius = 2;
				}
				for($bx = -$bradius; $bx <= $bradius; $bx++) {
					for($by = -$bradius; $by <= $bradius; $by++) {
						for($bz = -$bradius; $bz <= $bradius; $bz++) {
							$edge = (($bx == $bradius or $bx === -$bradius) && ($bz === $bradius or $bz === -$bradius)) or ($by === $bradius or $by === -$bradius);

							if($edge) {
								$level->setBlockIdAt($x + $bx, ($height + 1) + $by, $z + $bz, Block::IRON_BARS);
							}
						}
					}
				}
			}
			$level->setBlockIdAt($x, $height, $z, Block::BEDROCK);
			$level->setBlockIdAt($x, $height + 1, $z, Block::AIR);
		}
	}
}