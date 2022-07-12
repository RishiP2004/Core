<?php

declare(strict_types = 1);

namespace core\utils;

use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat;

abstract class CustomItem extends Item {
	private $maxStackSize = 1;

    public function __construct(ItemIdentifier $id, string $name = "Custom", string $customName = TextFormat::GRAY . "Custom", array $lore = [], int $maxStackSize = 64, array $enchants = [], array $tags = []) {
        parent::__construct($id, $name);

        $this->setCustomName($customName);
        $this->setLore($lore);

        foreach($enchants as $enchant) {
            $this->addEnchantment($enchant);
        }
        $this->getNamedTag()->setString($name, "true");

		$this->maxStackSize = $maxStackSize;
    }
	
	public function getMaxStackSize() : int {
		return $this->maxStackSize;
	}
}