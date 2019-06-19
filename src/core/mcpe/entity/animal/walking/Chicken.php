<?php

declare(strict_types = 1);

namespace core\mcpe\entity\animal\walking;

use core\mcpe\entity\AnimalBase;

use pocketmine\item\{
	ItemFactory,
	Item
};

class Chicken extends AnimalBase {
	const NETWORK_ID = self::CHICKEN;

	public $width = 1, $height = 0.8;

    public function initEntity() : void {
		$this->setMaxHealth(4);
        parent::initEntity();
    }

    public function getName() : string {
        return "Chicken";
    }

	public function entityBaseTick(int $tickDiff = 1) : bool {
		return parent::entityBaseTick($tickDiff);
		//TODO: Spawn egg item every 5-10 mins
		//TODO: Follow seeds
	}

	public function getDrops() : array {
		$drops = parent::getDrops();

		if(!$this->isBaby()) {
			if($this->isOnFire()) {
				$drops[] = ItemFactory::get(Item::COOKED_CHICKEN, 0, mt_rand(1, 3));
			} else {
				$drops[] = ItemFactory::get(Item::CHICKEN, 0, mt_rand(1, 3));
			}
		}
		$drops[] = ItemFactory::get(Item::FEATHER, 0, mt_rand(0, 2));
		return $drops;
	}

	public function getXpDropAmount() : int {
		$exp = parent::getXpDropAmount();

		if(!$this->isBaby()) {
			$exp += mt_rand(1, 3);
			return $exp;
		}
		return $exp;
	}
}
