<?php

declare(strict_types = 1);

namespace core\mcpe\entity\animal\walking;

use core\CorePlayer;

use pocketmine\item\{
	ItemFactory,
	Item,
	Shears,
	Bowl
};

use pocketmine\entity\{
	Entity,
	EntityIds
};

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

class Mooshroom extends Cow {
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
		//TODO: Sheep follow players holding wheat within 8 blocks
    }

    public function onCollideWithEntity(Entity $entity) : void {
		//TODO: Red mooshrooms convert to brown mooshrooms, and brown convert to red, when they are struck by lightning.
    }

    public function onPlayerLook(CorePlayer $player) : void {
		$hand = $player->getInventory()->getItemInHand();

		if(!$this->isBaby() and $hand instanceof Shears) {
			$this->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, "Shear");
		}
		if(!$this->isBaby() and $hand instanceof Bowl) {
			$this->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, "Mushroom Stew");
		}
		parent::onPlayerLook($player);
	}

    public function onPlayerInteract(CorePlayer $player) : void {
		$hand = $player->getInventory()->getItemInHand();

		if(!$this->isBaby() and $hand instanceof Shears) {
			$this->shear();
			$hand->applyDamage(1);
			$player->getInventory()->setItemInHand($hand);
			$this->level->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_SHEAR, 0, EntityIds::PLAYER);
		}
		if(!$this->isBaby() and $hand instanceof Bowl) {
			$hand = ItemFactory::get(Item::MUSHROOM_STEW);

			$player->getInventory()->setItemInHand($hand);
		}
		parent::onPlayerInteract($player);
    }

	public function shear() : self {
		$this->level->dropItem($this, ItemFactory::get(Item::RED_MUSHROOM, 0, 5));

		$cow = Cow::createEntity("Cow", $this->level, Cow::createBaseNBT($this, $this->motion, $this->yaw, $this->pitch));

		if($cow !== null) {
			$this->level->addEntity($cow);
			$this->flagForDespawn();
			$cow->spawnToAll();
		}
		return $this;
	}
}
