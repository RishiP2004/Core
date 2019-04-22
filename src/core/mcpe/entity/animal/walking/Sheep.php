<?php

namespace core\mcpe\entity\animal\walking;

use core\CorePlayer;

use core\mcpe\entity\{
    AnimalBase,
    Collidable,
	Interactable
};

use pocketmine\entity\Entity;

use pocketmine\item\{
	ItemFactory,
	Item,
	Shears
};

class Sheep extends AnimalBase implements Collidable, Interactable {
	public const NETWORK_ID = self::SHEEP;

	public $width = 1.2;
	public $height = 0.6;

	public function initEntity() : void {
		parent::initEntity();
	}

	public function getName() : string {
		return "Sheep";
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
		if($player->getInventory()->getItemInHand() instanceof Shears) {
			$this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_SHEARED, true);
		}
	}

	public function isSheared() : bool {
		return false;
	}

	public function setSheared(bool $sheared = true) : self {
	}

	public function shear() : self {
	}

	public function getDrops() : array {
		if($this->isSheared()) {
			return [];
		}
		return [
			ItemFactory::get(Item::WOOL),
			ItemFactory::get(Item::MUTTON, 0, mt_rand(1,3))
		];
	}
}