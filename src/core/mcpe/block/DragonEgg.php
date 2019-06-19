<?php

declare(strict_types = 1);

namespace core\mcpe\block;

use pocketmine\block\{
	Fallable,
	Air,
	Block
};

use pocketmine\item\Item;

use pocketmine\Player;

use pocketmine\math\Vector3;

use pocketmine\level\sound\GenericSound;

use pocketmine\level\Position;

class DragonEgg extends Fallable {
	protected $id = self::DRAGON_EGG;

	public function __construct($meta = 0) {
		$this->meta = $meta;
	}

	public function getName() : string {
		return "Dragon Egg";
	}

	public function getHardness() : float {
		return 4.5;
	}

	public function getBlastResistance() : float {
		return 45;
	}

	public function getLightLevel() : int {
		return 1;
	}

	public function isBreakable(Item $item) : bool {
		return false;
	}

	public function canBeActivated() : bool {
		return true;
	}

	public function onActivate(Item $item, Player $player = null) : bool {
		$level = $this->getLevel();

		for($c = 0; $c <= 16; $c++) {
			$x = $this->getX() + mt_rand(-15, 15);
			$y = $this->getY() + mt_rand(-7, 7);
			$z = $this->getZ() + mt_rand(-15, 15);

			if($level->getBlockIdAt($x, $y, $z) === Block::AIR && $level->isInWorld($x, $y, $z)) {
				$level->setBlock($this, new Air(), true, true);

				$oldPos = $this->asVector3();

				$level->setBlock(($pos = new Vector3($x, $y, $z)), $this, true, true);

				$posDelta = $pos->subtract($oldPos);
				$dist = $oldPos->distance($pos);

				for($c = 0; $c <= $dist; $c++) {
					$progress = $c / $dist;

					$this->getLevel()->addSound(new GenericSound(new Position($oldPos->x + $posDelta->x * $progress, 1.62 + $oldPos->y + $posDelta->y * $progress, $oldPos->z + $posDelta->z * $progress, $this->getLevel()), 2010));
				}
				return true;
			}
		}
		return true;
	}
}