<?php

declare(strict_types = 1);

namespace core\utils;

use pocketmine\item\Item;

use pocketmine\nbt\tag\{
    CompoundTag,
    NamedTag
};

abstract class CustomItem extends Item {
    const CUSTOM = "Custom";
	
	private $maxStackSize = 1;

    public function __construct(int $id, string $name = "Custom", array $lore = [], int $maxStackSize = 1, array $enchants = [], array $tags = [], int $meta = 0) {
        parent::__construct($id, $meta);

        $this->setCustomName($name);
        $this->setLore($lore);

        foreach($enchants as $enchant) {
            $this->addEnchantment($enchant);
        }
        if(!empty($tags)) {
            $this->setNamedTagEntry(new CompoundTag(self::CUSTOM));
            /** @var CompoundTag $compoundTag */
            $compoundTag = $this->getNamedTagEntry(self::CUSTOM);
            /** @var NamedTag $tag */
            foreach($tags as $tag) {
                $compoundTag->setTag($tag);
            }
        }
		$this->maxStackSize = $maxStackSize;
    }
	
	public function getMaxStackSize() : int {
		return $this->maxStackSize;
	}
}