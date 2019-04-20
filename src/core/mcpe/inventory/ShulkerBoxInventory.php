<?php

namespace core\mcpe\inventory;

use core\mcpe\tile\ShulkerBox;

use pocketmine\inventory\ContainerInventory;

use pocketmine\network\mcpe\protocol\types\WindowTypes;

use pocketmine\Player;

use pocketmine\level\Level;

use pocketmine\network\mcpe\protocol\{
	LevelSoundEventPacket,
	BlockEventPacket
};

use pocketmine\math\Vector3;

class ShulkerBoxInventory extends ContainerInventory {
	/** @var ShulkerBox */
	protected $holder;

	public function __construct(ShulkerBox $tile) {
		parent::__construct($tile);
	}

	public function getName() : string {
		return "Shulker Box";
	}

	public function getDefaultSize() : int {
		return 27;
	}

	public function getNetworkType() : int {
		return WindowTypes::CONTAINER;
	}

	public function onOpen(Player $who) : void {
		parent::onOpen($who);

		if(count($this->getViewers()) === 1 && ($level = $this->getHolder()->getLevel()) instanceof Level) {
			$this->broadcastBlockEventPacket($this->getHolder(), true);
			$level->broadcastLevelSoundEvent($this->getHolder()->add(0.5, 0.5, 0.5), LevelSoundEventPacket::SOUND_SHULKERBOX_OPEN);
		}
	}

	public function getHolder() : ShulkerBox {
		return $this->holder;
	}

	protected function broadcastBlockEventPacket(Vector3 $vector, bool $isOpen) {
		$pk = new BlockEventPacket();
		$pk->x = (int) $vector->x;
		$pk->y = (int) $vector->y;
		$pk->z = (int) $vector->z;
		$pk->eventType = 1;
		$pk->eventData = $isOpen ? 1 : 0;

		$this->getHolder()->getLevel()->addChunkPacket($this->getHolder()->getX() >> 4, $this->getHolder()->getZ() >> 4, $pk);
	}

	public function onClose(Player $who) : void {
		if(count($this->getViewers()) === 1 && ($level = $this->getHolder()->getLevel()) instanceof Level) {
			$this->broadcastBlockEventPacket($this->getHolder(), false);
			$level->broadcastLevelSoundEvent($this->getHolder()->add(0.5, 0.5, 0.5), LevelSoundEventPacket::SOUND_SHULKERBOX_CLOSED);
		}
		$this->getHolder()->saveNBT();
		parent::onClose($who);
	}

	public function sendContents($target) : void {
		parent::sendContents($target);
		$this->getHolder()->saveNBT();
	}
}