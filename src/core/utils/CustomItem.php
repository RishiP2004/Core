<?php

declare(strict_types = 1);

namespace core\utils;

use pocketmine\item\Item;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\utils\TextFormat;

abstract class CustomItem extends Item {
	private $maxStackSize = 1;

    public function __construct(int $id, int $meta = 0, string $name = "Custom", string $customName = TextFormat::GRAY . "Custom", array $lore = [], int $maxStackSize = 64, array $enchants = [], array $tags = []) {
        parent::__construct($id, $meta);

        $this->setCustomName($customName);
        $this->setLore($lore);

        foreach($enchants as $enchant) {
            $this->addEnchantment($enchant);
        }
		$this->setNamedTagEntry(new CompoundTag($name));

		$this->maxStackSize = $maxStackSize;
    }
	
	public function getMaxStackSize() : int {
		return $this->maxStackSize;
	}
}