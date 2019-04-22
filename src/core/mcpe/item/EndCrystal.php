<?php

namespace core\mcpe\item;

use pocketmine\item\Item;

use pocketmine\Player;

use pocketmine\block\Block;

use pocketmine\math\Vector3;

use pocketmine\entity\Entity;

class EndCrystal extends Item {
	public function __construct($meta = 0, $count = 1) {
		parent::__construct(Item::END_CRYSTAL, $meta, "Ender Crystal");
	}

	public function getMaxStackSize() : int {
		return 64;
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool {
		if(in_array($blockClicked->getId(), [Block::OBSIDIAN, Block::BEDROCK])) {
			$nbt = Entity::createBaseNBT($blockReplace->add(0.5, 0, 0.5));
			$crystal = Entity::createEntity("EnderCrystal", $player->getLevel(), $nbt);

			if($crystal instanceof \core\mcpe\entity\object\EndCrystal) {
				$crystal->spawnToAll();

				if($player->isSurvival()) {
					--$this->count;
				}
			}
		}
		return true;
	}
}