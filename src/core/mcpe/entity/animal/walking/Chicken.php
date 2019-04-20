<?php

namespace core\mcpe\entity\animal\walking;

use core\Core;

use core\mcpe\entity\AnimalBase;

use pocketmine\item\Item;

class Chicken extends AnimalBase {
	const NETWORK_ID = self::CHICKEN;

	public $width = 1, $height = 0.8;

    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
        return "Chicken";
    }

	public function entityBaseTick(int $tickDiff = 1) : bool {
		return parent::entityBaseTick($tickDiff);
	}

    public function getDrops() : array {
        $drops = [];

        if(Core::getInstance()->getMCPE()->drops()) {
            array_push($drops, Item::get(Item::FEATHER, 0, mt_rand(0, 2)));

            if($this->isOnFire()) {
                array_push($drops, Item::get(Item::COOKED_CHICKEN, 0, mt_rand(0, 1)));
            } else {
                array_push($drops, Item::get(Item::RAW_CHICKEN, 0, mt_rand(0, 1)));
            }
        }
        return $drops;
	}
}
