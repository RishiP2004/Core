<?php

namespace core\mcpe\block;

use core\utils\Entity;

use core\mcpe\entity\monster\walking\{
    SnowGolem,
    IronGolem
};

use pocketmine\item\Item;

use pocketmine\block\{
    Block,
    Air
};

use pocketmine\math\Vector3;

use pocketmine\Player;

class LitPumpkin extends \pocketmine\block\LitPumpkin {
	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
		$parent = parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);

		if($player instanceof Player) {
			$level = $this->getLevel();

			if(Entity::checkSnowGolemStructure($this)[0]){
			    $level->setBlock($this, 0);
			    $level->setBlock($this->subtract(0, 1), new Air());
			    $level->setBlock($this->subtract(0, 2), new Air());

			    $golem = Entity::createEntity(Entity::SNOW_GOLEM, $level, Entity::createBaseNBT($this));

			    if($golem instanceof SnowGolem) {
			        $golem->spawnToAll();
			    }
			}
			$check = Entity::checkIronGolemStructure($this);

			if($check[0]) {
			    switch($check[1]) {
			        case "X":
			            $level->setBlock($this->subtract(1, 1, 0), new Air());
			            $level->setBlock($this->add(1, -1, 0), new Air());
			        break;
			        case "Z":
			            $level->setBlock($this->subtract(0, 1, 1), new Air());
			            $level->setBlock($this->add(0, -1, 1), new Air());
					break;
			    }
			    $level->setBlock($this, new Air());
			    $level->setBlock($this->subtract(0, 1), new Air());
			    $level->setBlock($this->subtract(0, 2),new Air());

			    $golem = Entity::createEntity(Entity::IRON_GOLEM, $level, Entity::createBaseNBT($this));

			    if($golem instanceof IronGolem) {
			    	$golem->spawnToAll();
			    }
			}
		}
		return $parent;
	}
}