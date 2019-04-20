<?php

namespace core\mcpe\entity\monster\walking;

use core\CorePlayer;

use core\mcpe\entity\{
	CreatureBase,
	Collidable,
	Interactable
};

use pocketmine\item\{
	Item,
	Shears
};

use pocketmine\block\{
	Water,
	Lava
};

use pocketmine\entity\Entity;

class SnowGolem extends CreatureBase implements Collidable, Interactable {
	public const NETWORK_ID = self::SNOW_GOLEM;

	public $width = 1.281, $height = 1.875;

	public function initEntity() : void {
		if($this->namedtag->getByte("Pumpkin", 1, true) === 1) {
			$this->setPumpkin(true);
		} else {
			$this->setPumpkin(false);
		}
		parent::initEntity();
	}

	public function getName() : string {
		return "Snow Golem";
	}

	public function entityBaseTick(int $tickDiff = 1) : bool {
		$biome = $this->level->getBiome($this->getFloorX(), $this->getFloorZ());

		if($biome->getTemperature() > 1)
			$this->setOnFire(200);

		if($this->level->getBlockLightAt($this->getFloorX(), $this->getFloorY(), $this->getFloorZ()) >= 14 or
			$this->level->getBlock($this, true, false) instanceof Water or
			$this->level->getBlock($this, true, false) instanceof Lava) { // TODO: check weather
			$this->setOnFire(200);
		}
		// TODO: only make snow in biomes with less than 0.8 temp
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
		if($player->getInventory()->getItemInHand() instanceof Shears) {
			$this->setPumpkin(false);
		}
	}

	public function setPumpkin(bool $wearing = true) : self {
		$this->namedtag->setByte("Pumpkin", $wearing);
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SHEARED, !$wearing);
		return $this;
	}

	public function hasPumpkin() : bool {
		return $this->namedtag->getByte("Pumpkin", 1, true);
	}

	public function getDrops() : array {
		return [
			Item::get(Item::SNOWBALL, 0, mt_rand(0, 15))
		];
	}
}