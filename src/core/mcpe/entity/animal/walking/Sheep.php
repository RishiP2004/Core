<?php

declare(strict_types = 1);

namespace core\mcpe\entity\animal\walking;

use core\CorePlayer;
use core\mcpe\Addon;
use core\mcpe\entity\{
    AnimalBase,
    Collidable,
	Interactable
};

use pocketmine\entity\{
	Entity,
	EntityIds
};

use pocketmine\item\{
	ItemFactory,
	Item,
	Shears,
	Dye
};

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

class Sheep extends AnimalBase implements Collidable, Interactable, Addon {
	public const NETWORK_ID = self::SHEEP;

	public $width = 1.2;
	public $height = 0.6;
	private $colorMeta = self::WHITE;
	private $sheared = false;

	public function initEntity() : void {
		parent::initEntity();

		if((bool) $this->namedtag->getByte("Sheared", 0)) {
			$this->setSheared(true);
		} else {
			$this->setSheared(false);
		}
		$chance = mt_rand(1, 1000);

		if($chance <= 50) {
			$colorMeta = self::LIGHT_GRAY;
		} elseif($chance >= 51 and $chance <= 100) {
			$colorMeta = self::GRAY;
		} elseif($chance >= 101 and $chance <= 150) {
			$colorMeta = self::BLACK;
		} elseif($chance >= 151 and $chance <= 180) {
			$colorMeta = self::BROWN;
		} elseif($chance >= 181 and $chance <= 183) {
			$colorMeta = self::PINK;
		} else {
			$colorMeta = self::WHITE;
		}
		if($this->namedtag->getByte("Color", self::WHITE) !== null) {
			$colorMeta = $this->namedtag->getByte("Color", self::WHITE);
		}
		$this->setColor($colorMeta);

		if(mt_rand(1, 100) <= 5) {
			$this->setBaby(true);
		}
	}

	public function getName() : string {
		return "Sheep";
	}

	public function entityBaseTick(int $tickDiff = 1) : bool {
		//TODO: Eat grass to recover wool
		//TODO: Sheep will follow a player holding wheat within a radius of 8 blocks
		return parent::entityBaseTick($tickDiff);
	}

	public function onCollideWithEntity(Entity $entity) : void {
	}

	public function onPlayerLook(CorePlayer $player) : void {
		$hand = $player->getInventory()->getItemInHand();

		if(!$this->isBaby() and $hand instanceof Shears and !$this->sheared) {
			$this->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, "Shear");
		}
		if($hand instanceof Dye and !$this->sheared) {
			$this->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, "Dye");
		}
	}

	public function onPlayerInteract(CorePlayer $player) : void {
		$hand = $player->getInventory()->getItemInHand();

		if(!$this->isBaby() and $hand instanceof Shears and !$this->sheared) {
			$this->shear();
			//$hand->applyDamage(1);
			$player->getInventory()->setItemInHand($hand);
			$this->level->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_SHEAR, 0, EntityIds::PLAYER);
		}
		if($hand instanceof Dye and !$this->sheared) {
			$this->setColor($hand->pop()->getDamage());
			$player->getInventory()->setItemInHand($hand);
		}
	}

	public function isSheared() : bool {
		return false;
	}

	public function setSheared(bool $sheared = true) : self {
		$this->sheared = $sheared;

		$this->setGenericFlag(self::DATA_FLAG_SHEARED, $sheared);
		$this->namedtag->setByte("Sheared", (int)$sheared);
		return $this;
	}

	public function shear() : self {
		$this->level->dropItem($this, ItemFactory::get(Item::WOOL, $this->colorMeta, mt_rand(1, 3)));
		$this->setSheared(true);
		return $this;
	}

	public function getColor() : int {
		return $this->colorMeta;
	}

	public function setColor(int $colorMeta) : self {
		if($colorMeta >= 0 and $colorMeta <= 15) {
			$this->colorMeta = $colorMeta;

			$this->getDataPropertyManager()->setPropertyValue(self::DATA_COLOUR, self::DATA_TYPE_BYTE, $colorMeta);
		} else {
			throw new \OutOfRangeException("Meta value provided is out of range 0 - 15");
		}
		return $this;
	}

	public function getDrops() : array {
		if(!$this->isBaby()) {
			$drops = [];

			if($this->isOnFire()) {
				$drops[] = ItemFactory::get(Item::COOKED_MUTTON, 0, mt_rand(1, 3));
			} else {
				$drops[] = ItemFactory::get(Item::MUTTON, 0, mt_rand(1, 3));
			}
			if($this->isSheared()) {
				return $drops;
			}
			$drops[] = ItemFactory::get(Item::WOOL, $this->colorMeta);
			return $drops;
		} else {
			return [];
		}
	}

	public function getXpDropAmount() : int {
		if(!$this->isBaby()) {
			return mt_rand(1, 3);
		}
		return parent::getXpDropAmount();
	}
}