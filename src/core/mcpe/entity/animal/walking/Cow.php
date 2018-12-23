<?php

namespace core\mcpe\entity\animal\walking;

use core\Core;
use core\CorePlayer;

use core\mcpe\entity\{
	AnimalBase,
	Collidable,
	Interactable
};

use pocketmine\entity\Entity;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\item\Item;

class Cow extends AnimalBase implements Collidable, Interactable {
    const NETWORK_ID = self::COW;

	public $width = 1.5, $height = 1.2, $eyeHeight = 1;
	
    public function initEntity(CompoundTag $tag) : void {
        parent::initEntity($tag);
    }

    public function getName() : string {
        return "Cow";
    }
	
	public function entityBaseTick(int $tickDiff = 1) : bool {
		return parent::entityBaseTick($tickDiff);
	}
	
	public function onCollideWithEntity(Entity $entity) : void {
		// TODO: Implement onCollideWithEntity() method.
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
