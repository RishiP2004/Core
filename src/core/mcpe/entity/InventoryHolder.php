<?php

declare(strict_types = 1);

namespace core\mcpe\entity;

use pocketmine\item\Item;

interface InventoryHolder {
	public function isDropAll() : bool;

	public function setDropAll(bool $dropAll = true);

	public function equipRandomItems() : void;

	public function equipRandomArmour() : void;

	public function checkItemValueToMainHand(Item $item) : bool;

	public function checkItemValueToOffHand(Item $item) : bool;
}