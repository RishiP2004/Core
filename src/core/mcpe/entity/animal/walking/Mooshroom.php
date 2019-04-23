<?php

declare(strict_types = 1);

namespace core\mcpe\entity\animal\walking;

use core\CorePlayer;

use core\mcpe\entity\{
    Collidable,
    Interactable
};

use pocketmine\item\Shears;

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
    }

    public function onPlayerLook(CorePlayer $player) : void {
		if($player->getInventory()->getItemInHand() instanceof Shears) {
			$this->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, "Shear");
		}
	}

    public function onPlayerInteract(CorePlayer $player) : void {
    }

	public function getDrops() : array {
		return [];
	}
}
