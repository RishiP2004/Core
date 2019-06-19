<?php

declare(strict_types = 1);

namespace core\mcpe\entity\animal\walking;

use core\CorePlayer;

use core\mcpe\entity\{
    AnimalBase,
    Collidable,
    Interactable
};
use core\mcpe\item\Saddle;

use pocketmine\entity\{
	Entity,
	Rideable
};

use pocketmine\item\{
	ItemFactory,
	Item
};

class Pig extends AnimalBase implements Collidable, Interactable, Rideable {
    const NETWORK_ID = self::PIG;

    public $width = 1.5, $height = 1.0;

    private $saddle = false;

    public function initEntity() : void {
        $this->setMaxHealth(10);
        parent::initEntity();

		if((bool) $this->namedtag->getByte("Saddle", 0)) {
			$this->setSaddled(true);
		}
    }

    public function getName() : string {
        return "Pig";
    }

    public function entityBaseTick(int $tickDiff = 1) : bool {
        return parent::entityBaseTick($tickDiff);
		//TODO: Follow carrots within 8 blocks
    }

    public function onCollideWithEntity(Entity $entity) : void {
    }

    public function onPlayerInteract(CorePlayer $player) : void {
    }

	public function onPlayerLook(CorePlayer $player) : void {
		$hand = $player->getInventory()->getItemInHand();

		if(!$this->isBaby() and $hand->getId() instanceof Saddle) {
			$this->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, "Saddle");
		}
	}

	public function isSaddled() : bool {
		return $this->saddled;
	}

	public function setSaddled(bool $saddled) : self {
		$this->saddled = $saddled;

		$this->namedtag->setByte("Saddle", (int)$saddled);
		$this->setGenericFlag(self::DATA_FLAG_SADDLED, $saddled);
		return $this;
	}

    public function getDrops() : array {
		$drops = parent::getDrops();

		if(!$this->isBaby()) {
			if($this->isOnFire()) {
				$drops[] = ItemFactory::get(Item::COOKED_PORKCHOP, 0, mt_rand(1, 3));
			} else {
				$drops[] = ItemFactory::get(Item::PORKCHOP, 0, mt_rand(1, 3));
			}
			if(!empty($this->getArmorInventory()->getContents())) {
				$drops = array_merge($drops, $this->getArmorInventory()->getContents());
			}
		}
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