<?php

declare(strict_types = 1);

namespace core\mcpe\inventory;

use core\mcpe\tile\Beacon;

use pocketmine\inventory\ContainerInventory;

use pocketmine\network\mcpe\protocol\types\WindowTypes;

class BeaconInventory extends ContainerInventory {
	public function __construct(Beacon $tile) {
		parent::__construct($tile);
	}

	public function getNetworkType() : int {
		return WindowTypes::BEACON;
	}

	public function getName() : string {
		return "Beacon";
	}

	public function getDefaultSize() : int {
		return 1;
	}

	public function getHolder() {
		return $this->holder;
	}
}