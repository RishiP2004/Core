<?php

namespace core\mcpe\block;

use pocketmine\item\{
	Item,
	FlintSteel
};

use pocketmine\Player;

use pocketmine\block\Block;

use pocketmine\math\Vector3;

class Obsidian extends \pocketmine\block\Obsidian {
	public function onActivate(Item $item, Player $player = null) : bool {
		if($item instanceof FlintSteel) {
			$x_max = $x_min = $this->x;
			for($x = $this->x + 1; $this->level->getBlockIdAt($x, $this->y, $this->z) === Block::OBSIDIAN; $x++) {
				$x_max++;
			}
			for($x = $this->x - 1; $this->level->getBlockIdAt($x, $this->y, $this->z) === Block::OBSIDIAN; $x--) {
				$x_min--;
			}
			$count_x = $x_max - $x_min + 1;

			if($count_x >= 4 and $count_x <= 23) {
				$x_max_y = $this->y;
				$x_min_y = $this->y;

				for($y = $this->y; $this->level->getBlockIdAt($x_max, $y, $this->z) == Block::OBSIDIAN; $y++) {
					$x_max_y++;
				}
				for($y = $this->y; $this->level->getBlockIdAt($x_min, $y, $this->z) == Block::OBSIDIAN; $y++) {
					$x_min_y++;
				}
				$y_max = min($x_max_y, $x_min_y) - 1;
				$count_y = $y_max - $this->y + 2;

				if($count_y >= 5 and $count_y <= 23) {
					$count_up = 0;
					for($ux = $x_min; ($this->level->getBlockIdAt($ux, $y_max, $this->z) == Block::OBSIDIAN and $ux <= $x_max); $ux++) {
						$count_up++;
					}
					if($count_up === $count_x) {
						for($px = $x_min + 1; $px < $x_max; $px++) {
							for($py = $this->y + 1; $py < $y_max; $py++) {
								$this->level->setBlock(new Vector3($px, $py, $this->z), new Portal());
							}
						}
						if($player->isSurvival()) {
							$item = clone $item;
							$item->applyDamage(1);
							$player->getInventory()->setItemInHand($item);
						}

						return true;
					}
				}
			}

			$z_max = $z_min = $this->z;
			for(
				$z = $this->z + 1; $this->level->getBlockIdAt($this->x, $this->y, $z) == Block::OBSIDIAN; $z++
			) {
				$z_max++;
			}
			for(
				$z = $this->z - 1; $this->level->getBlockIdAt($this->x, $this->y, $z) == Block::OBSIDIAN; $z--
			) {
				$z_min--;
			}
			$count_z = $z_max - $z_min + 1;
			if($count_z >= 4 and $count_z <= 23) {
				$z_max_y = $this->y;
				$z_min_y = $this->y;
				for(
					$y = $this->y; $this->level->getBlockIdAt($this->x, $y, $z_max) == Block::OBSIDIAN; $y++
				) {
					$z_max_y++;
				}
				for(
					$y = $this->y; $this->level->getBlockIdAt($this->x, $y, $z_min) == Block::OBSIDIAN; $y++
				) {
					$z_min_y++;
				}
				$y_max   = min($z_max_y, $z_min_y) - 1;
				$count_y = $y_max - $this->y + 2;
				if($count_y >= 5 and $count_y <= 23) {
					$count_up = 0;
					for(
						$uz = $z_min; ($this->level->getBlockIdAt($this->x, $y_max, $uz) == Block::OBSIDIAN and $uz <= $z_max); $uz++
					) {
						$count_up++;
					}
					if($count_up == $count_z) {
						for($pz = $z_min + 1; $pz < $z_max; $pz++) {
							for($py = $this->y + 1; $py < $y_max; $py++) {
								$this->level->setBlock(new Vector3($this->x, $py, $pz), new Portal());
							}
						}
						if($player->isSurvival()) {
							$item = clone $item;
							$item->applyDamage(1);
							$player->getInventory()->setItemInHand($item);
						}

						return true;
					}
				}
			}
		}

		return false;
	}


	public function onBreak(Item $item, Player $player = null) : bool {
        parent::onBreak($item);

        for($i = 0; $i <= 6; $i++) {
            if($this->getSide($i)->getId() == self::PORTAL) {
                break;
            }
            if($i === 6) {
                return false;
            }
        }
        $block = $this->getSide($i);

        if($this->getLevel()->getBlock($this->temporalVector->setComponents($block->x - 1, $block->y, $block->z))->getId() === Block::PORTAL or $this->getLevel()->getBlock($this->temporalVector->setComponents($block->x + 1, $block->y, $block->z))->getId() === Block::PORTAL) {
            for($x = $block->x; $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $block->y, $block->z))->getId() === Block::PORTAL; $x++) {
                for($y = $block->y; $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y, $block->z))->getId() === Block::PORTAL; $y++) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($x, $y, $block->z), new Block(0));
                }
                for($y = $block->y - 1; $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y, $block->z))->getId() === Block::PORTAL; $y--) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($x, $y, $block->z), new Block(0));
                }
            }
            for($x = $block->x - 1; $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $block->y, $block->z))->getId() === Block::PORTAL; $x--) {
                for($y = $block->y; $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y, $block->z))->getId() === Block::PORTAL; $y++) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($x, $y, $block->z), new Block(0));
                }
                for($y = $block->y - 1; $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y, $block->z))->getId() === Block::PORTAL; $y--) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($x, $y, $block->z), new Block(0));
                }
            }
        } else {
            for($z = $block->z; $this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $block->y, $z))->getId() === Block::PORTAL; $z++) {
                for($y = $block->y; $this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $y, $z))->getId() === Block::PORTAL; $y++) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($block->x, $y, $z), new Block(0));
                }
                for($y = $block->y - 1; $this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $y, $z))->getId() === Block::PORTAL; $y--) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($block->x, $y, $z), new Block(0));
                }
            }
            for($z = $block->z - 1; $this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $block->y, $z))->getId() === Block::PORTAL; $z--) {
                for($y = $block->y; $this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $y, $z))->getId() === Block::PORTAL; $y++) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($block->x, $y, $z), new Block(0));
                }
                for($y = $block->y - 1; $this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $y, $z))->getId() === Block::PORTAL; $y--) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($block->x, $y, $z), new Block(0));
                }
            }
        }
        return true;
    }
}