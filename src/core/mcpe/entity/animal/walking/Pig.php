<?php

namespace core\mcpe\entity\animal\walking;

use core\CorePlayer;

use core\mcpe\entity\{
    AnimalBase,
    Collidable,
    Interactable
};

use pocketmine\entity\Entity;

use pocketmine\item\Item;

class Pig extends AnimalBase implements Collidable, Interactable {
    const NETWORK_ID = self::PIG;

    public $width = 1.5, $height = 1.0;

    public function initEntity() : void {
        $this->setMaxHealth(10);
        parent::initEntity();
    }

    public function getName() : string {
        return "Pig";
    }

    public function entityBaseTick(int $tickDiff = 1) : bool {
        return parent::entityBaseTick($tickDiff);
    }

    public function onCollideWithEntity(Entity $entity) : void {
    }

    public function onPlayerInteract(CorePlayer $player) : void {
    }

    public function getXpDropAmount() : int {
        //TODO: check for baby state
        return mt_rand(1, 3);
    }

    public function getDrops() : array {
		$drops = [];

		if($this->isOnFire()) {
			array_pad($drops, mt_rand(1, 3), Item::get(Item::COOKED_PORKCHOP));
		} else {
			array_pad($drops, mt_rand(1, 3), Item::get(Item::RAW_PORKCHOP));
		}
		if(!empty($this->getArmorInventory()->getContents())) {
			array_merge($drops, $this->getArmorInventory()->getContents());
		}
		return $drops;
    }
}