<?php

namespace core\mcpe\inventory;

use core\mcpe\tile\Hopper;

use pocketmine\inventory\ContainerInventory;

use pocketmine\network\mcpe\protocol\types\WindowTypes;

class HopperInventory extends ContainerInventory {
    public function __construct(Hopper $tile) {
        parent::__construct($tile);
    }

    public function getNetworkType() : int {
        return WindowTypes::HOPPER;
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