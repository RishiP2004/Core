<?php

namespace core\mcpe\block;

use core\Core;
use core\CorePlayer;

use core\mcpe\tile\MobSpawner;

//use factions\FactionsPlayer;

use pocketmine\item\Item;

use pocketmine\Player;

use pocketmine\tile\Tile;

use pocketmine\block\Block;

use pocketmine\math\Vector3;

use pocketmine\item\enchantment\Enchantment;

use pocketmine\item\ItemFactory;

use pocketmine\nbt\tag\{
    CompoundTag,
    StringTag,
    IntTag
};

class MonsterSpawner extends \pocketmine\block\MonsterSpawner {
    private $entityId = 0;

    public function __construct($meta = 0) {
        parent::__construct($meta);
    }

    public function canBeActivated() : bool {
        return true;
    }

    public function onActivate(Item $item, Player $player = null) : bool {
        if($this->entityId !== 0 or $item->getId() !== Item::SPAWN_EGG) {
            return false;
        }
        if($player instanceof CorePlayer) {
            $tile = $this->getLevel()->getTile($this);

            if($this->entityId === 0) {
                if($item->getId() === Item::SPAWN_EGG) {
                    $this->entityId = $item->getDamage();

                    if(!$tile instanceof MobSpawner) {
                        $nbt = new CompoundTag("", [
                            new StringTag(Tile::TAG_ID, Tile::MOB_SPAWNER),
                            new IntTag(Tile::TAG_X, $this->x),
                            new IntTag(Tile::TAG_Y, $this->y),
                            new IntTag(Tile::TAG_Z, $this->z),
                        ]);
                        $tile = Tile::createTile(Tile::MOB_SPAWNER, $this->getLevel(), $nbt);
                    }
                    $tile->setEntityId($this->entityId);
                    return true;
                }
				if(Core::getInstance()->getNetwork()->getServerFromIp(Core::getInstance()->getServer()->getIp())->getName() === "Factions") {
                    /**if($player instanceof FactionsPlayer) {
                        if($player->isSneaking()) {
                            if($tile instanceof MobSpawner) {
                                $player->sendSpawnersTierMenu($tile);
                                return false;
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

        $tile = $this->getLevel()->getTile($this);
        $this->entityId = $item->getDamage();
        $this->meta = 0;

        $this->getLevel()->setBlock($this, $this, true, false);

        if(!$tile instanceof MobSpawner) {
            /** @var CompoundTag $nbt */
            $nbt = new CompoundTag ("", [
                new StringTag(Tile::TAG_ID, Tile::MOB_SPAWNER),
                new IntTag(Tile::TAG_X, (int)$this->x),
                new IntTag(Tile::TAG_Y, (int)$this->y),
                new IntTag(Tile::TAG_Z, (int)$this->z)
            ]);
            /** @var MobSpawner $tile */
            $tile = Tile::createTile(Tile::MOB_SPAWNER, $this->getLevel(), $nbt);

            $tile->setEntityId($this->entityId);
            return true;
        }
        return true;
    }

    public function getDrops(Item $item) : array {
        $tile = $this->getLevel()->getTile($this);

        if($tile instanceof MobSpawner) {
            if($item->hasEnchantment(Enchantment::SILK_TOUCH)) {
                return [
                    ItemFactory::get($this->getItemId(), $tile->getEntityId(), 1, $this->getLevel()->getTile($this)->getCleanedNBT()->namedTag)
                ];
            }
        }
        return [];
    }

    public function getSilkTouchDrops(Item $item) : array {
        return [];
    }
}