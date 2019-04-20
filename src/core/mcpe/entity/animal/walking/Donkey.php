<?php

namespace core\mcpe\entity\animal\walking;

use core\Core;

use core\mcpe\entity\{
	AnimalBase,
	Collidable
};

use pocketmine\entity\Entity;

use pocketmine\item\Item;

class Donkey extends AnimalBase implements Collidable {
    const NETWORK_ID = self::DONKEY;

	public $width = 1.2, $height = 1.562;
	
    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
        return "Donkey";
    }

	public function entityBaseTick(int $tickDiff = 1) : bool {
		return parent::entityBaseTick($tickDiff);
	}
	
	public function onCollideWithEntity(Entity $entity) : void {
		// TODO: Implement onCollideWithEntity() method.
	}

    public function getDrops() : array {
        if(Core::getInstance()->getMCPE()->drops()) {
            return [
                Item::get(Item::LEATHER, 0, mt_rand(0, 2))
            ];
        } else {
            return [];
        }
    }
}

