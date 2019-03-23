<?php

namespace core\mcpe\block;

use core\CorePlayer;

use core\mcpe\tile\MobSpawner;

//use factions\FactionsPlayer;

use pocketmine\item\Item;

use pocketmine\Player;

use pocketmine\tile\Tile;

use pocketmine\block\Block;

use pocketmine\math\Vector3;

use pocketmine\item\ItemFactory;

class MonsterSpawner extends \pocketmine\block\MonsterSpawner {
    private $entityId = 0;

    public function __construct($meta = 0) {
        parent::__construct($meta);
    }

    public function canBeActivated() : bool {
        return true;
    }

    public function onActivate(Item $item, Player $player = null) : bool {
        if($item->getId() === Item::SPAWN_EGG) {
            $tile = $this->getLevel()->getTile($this);

            if(!$tile instanceof MobSpawner) {
                $nbt = MobSpawner::createNBT($this);
                $tile = Tile::createTile(Tile::MOB_SPAWNER, $this->getLevel(), $nbt);

                if($tile instanceof MobSpawner) {
                    $tile->setEntityId($item->getDamage());

                    if(!$player->isCreative()) {
                        $item->pop();
                    }
                    return true;
                    /**if(Core::getInstance()->getNetwork()->getServerFromIp(Core::getInstance()->getServer()->getIp())->getName() === "Factions") {
                        if($player instanceof FactionsPlayer) {
                            if($player->isSneaking()) {
                                $player->sendSpawnersTierMenu($tile);
                                return true;
                            }
                        }
                    }*/
                }
            }
        }
        return false;
    }

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool {
        parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);

        $eID = null;
		$nbt = MobSpawner::createNBT($this, $face, $item, $player);

		if($item->getNamedTag()->getTag(MobSpawner::TAG_ENTITY_ID) !== null) {
			foreach([MobSpawner::TAG_ENTITY_ID,
					MobSpawner::TAG_DELAY,
					MobSpawner::TAG_MIN_SPAWN_DELAY,
					MobSpawner::TAG_MAX_SPAWN_DELAY,
					MobSpawner::TAG_SPAWN_COUNT,
					MobSpawner::TAG_SPAWN_RANGE] as $tag_name) {
                $tag = $item->getNamedTag()->getTag($tag_name);
				
                if($tag !== null) {
                    $nbt->setTag($tag);
                }
            }
        } else if(($meta = $item->getDamage()) !== 0) {
			$nbt->setInt(MobSpawner::TAG_ENTITY_ID, $meta);
		} else {
			return true;
		}
		Tile::createTile(Tile::MOB_SPAWNER, $this->getLevel(), $nbt);
		return true;
    }

    public function getSilkTouchDrops(Item $item) : array {
		$tile = $this->getLevel()->getTile($this);

		if($tile instanceof MobSpawner){
			return [
				ItemFactory::get(Item::MONSTER_SPAWNER, 0, 1, $tile->getCleanedNBT()),
			];
		}
		return parent::getSilkTouchDrops($item);
    }
}