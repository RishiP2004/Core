<?php

namespace core\mcpe\block;

use pocketmine\block\Fallable;

use pocketmine\item\Item;

use pocketmine\Player;

use pocketmine\level\{
    Level,
    Position
};

use pocketmine\block\Air;

use pocketmine\math\Vector3;

use pocketmine\level\sound\GenericSound;

class DragonEgg extends Fallable {
    const RAND_VERTICAL = [-7, -6, -5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5, 6, 7];
    const RAND_HORIZONTAL = [-15, -14, -13, -12, -11, -10, -9, -8, -7, -6, -5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];
    /** @var int $id */
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
        $found = false;
        $level = $this->getLevel();
        $x = $y = $z = 0;

        for($c = 0; $c <= 16; $c++) {
            $x = $this->getX() + self::RAND_HORIZONTAL[array_rand(self::RAND_HORIZONTAL)];
            $y = $this->getY() + self::RAND_VERTICAL[array_rand(self::RAND_VERTICAL)];
            $z = $this->getZ() + self::RAND_HORIZONTAL[array_rand(self::RAND_HORIZONTAL)];

            if($level->getBlockIdAt($x, $y, $z) == 0 && $y < Level::Y_MAX && $y > 0){
                $found = true;
                break;
            }
        }
        if($found) {
            $level->setBlock($this, new Air(), true, true);

            $oldpos = clone $this;
            $pos = new Vector3($x, $y, $z);
            $newpos = clone $pos;

            $level->setBlock($pos, $this, true, true);

            $posdistance = new Vector3($newpos->x - $oldpos->x, $newpos->y - $oldpos->y, $newpos->z - $oldpos->z);
            $intdistance = $oldpos->distance($newpos);

            for($c = 0; $c <= $intdistance; $c++) {
                $progress = $c / $intdistance;

                $this->getLevel()->addSound(new GenericSound(new Position($oldpos->x + $posdistance->x * $progress, 1.62 + $oldpos->y + $posdistance->y * $progress, $oldpos->z + $posdistance->z * $progress, $this->getLevel()), 2010));
            }
        }
        return true;
    }
}