<?php

namespace core\mcpe\tile;

use core\mcpe\inventory\ShulkerBoxInventory;

use pocketmine\tile\{
	Spawnable,
	Container,
	Nameable,
	NameableTrait,
	ContainerTrait
};

use pocketmine\inventory\InventoryHolder;

use pocketmine\nbt\tag\CompoundTag;

class ShulkerBox extends Spawnable implements InventoryHolder, Container, Nameable {
	use NameableTrait, ContainerTrait;
	/** @var ShulkerBoxInventory */
	protected $inventory;

	public function getDefaultName() : string {
		return "Shulker Box";
	}

	public function getRealInventory() {
		return $this->inventory;
	}

	public function getInventory() {
		return $this->inventory;
	}

	protected function readSaveData(CompoundTag $nbt) : void {
		$this->loadName($nbt);

		$this->inventory = new ShulkerBoxInventory($this);

		$this->loadItems($nbt);
	}

	protected function writeSaveData(CompoundTag $nbt) : void {
		$this->saveName($nbt);
		$this->saveItems($nbt);
	}

	public function close() : void {
		if(!$this->isClosed()){
			$this->inventory->removeAllViewers(true);

			$this->inventory = null;
			parent::close();
		}
	}
}