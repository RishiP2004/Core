<?php

namespace core\mcpe\entity\animal\walking;

use core\Core;
use core\CorePlayer;

use core\mcpe\entity\{
    Collidable,
    Interactable
};

use pocketmine\item\{
	Item,
	Shears
};

use pocketmine\entity\Entity;

class Mooshroom extends Cow implements Collidable, Interactable {
    const NETWORK_ID = self::MOOSHROOM;

    public $width = 1.781, $height = 1.875;

    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
        return "Mooshroom";
    }

    public function entityBaseTick(int $tickDiff = 1) : bool {
        return parent::entityBaseTick($tickDiff);
    }

    public function onCollideWithEntity(Entity $entity) : void {
	// TODO: Implement onCollideWithEntity() method.
    }

    public function onPlayerLook(CorePlayer $player) : void {
		if($player->getInventory()->getItemInHand() instanceof Shears) {
			$this->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, "Shear");
		}
	}

    public function onPlayerInteract(CorePlayer $player) : void {
		// TODO: Implement onPlayerInteract() method.
    }

	public function getDrops() : array {
		$drops = [];

		if(Core::getInstance()->getMCPE()->drops()) {
			array_push($drops, Item::get(Item::LEATHER, 0, mt_rand(0, 2)));

			if($this->isOnFire()) {
				array_push($drops, Item::get(Item::COOKED_BEEF, 0, mt_rand(1, 3)));
			} else {
				array_push($drops, Item::get(Item::RAW_BEEF, 0, mt_rand(1, 3)));
			}
		}
		return $drops;
	}
}
