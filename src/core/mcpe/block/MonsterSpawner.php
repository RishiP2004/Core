<?php

declare(strict_types = 1);

namespace core\mcpe\block;

//use factions\FactionsPlayer;

use core\mcpe\tile\MobSpawner;

use pocketmine\Player;

use pocketmine\item\{
	Item,
	SpawnEgg
};

use pocketmine\block\Block;

use pocketmine\math\Vector3;

class MonsterSpawner extends \pocketmine\block\MonsterSpawner {
    public function __construct($meta = 0) {
        parent::__construct($meta);
    }

    public function canBeActivated() : bool {
        return true;
    }

    public function onActivate(Item $item, Player $player = null) : bool {
		if($item instanceof SpawnEgg) {
			$tile = $this->getLevel()->getTile($this);

			if($tile instanceof MobSpawner) {
				$spawner = $tile;
			} else {
				/** @var MobSpawner $spawner */
				$spawner = MobSpawner::createTile(MobSpawner::MOB_SPAWNER, $this->getLevel(), MobSpawner::createNBT($this));
				/**
				if(Core::getInstance()->getNetwork()->getServerFromIp(Core::getInstance()->getServer()->getIp())->getName() === "Factions") {
					if($player instanceof FactionsPlayer) {
						if($player->isSneaking()) {
							$player->sendSpawnersTierMenu($tile);
							return true;
						}
					}
				}
				 */
			}
			$spawner->setEntityId($item->getDamage());
		}
        return false;
    }

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
		if($item->getDamage() > 9) {
			$this->meta = 0;
			$return = parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
			/** @var MobSpawner $tile */
			$tile = MobSpawner::createTile(MobSpawner::MOB_SPAWNER, $this->getLevel(), MobSpawner::createNBT($this));

			$tile->setEntityId($item->getDamage());
		} else {
			$return = parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}
		return $return;
    }

	public function getLightLevel() : int {
		return 3;
	}
}