<?php

declare(strict_types = 1);

namespace core\mcpe\block;

use core\utils\Level;

use pocketmine\Player;

use pocketmine\item\Item;

use pocketmine\network\mcpe\protocol\types\DimensionIds;

use pocketmine\level\Explosion;

class Bed extends \pocketmine\block\Bed {
    public function onActivate(Item $item, Player $player = null) : bool {
        $dimension = Level::getDimension($this->getLevel());

        if($dimension === DimensionIds::NETHER or $dimension == DimensionIds::THE_END) {
            $explosion = new Explosion($this, 6);

            $explosion->explodeA();
            $explosion->explodeB();
            return true;
        }
        return parent::onActivate($item, $player);
    }
}