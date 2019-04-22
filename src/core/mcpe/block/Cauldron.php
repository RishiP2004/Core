<?php

namespace core\mcpe\block;

use core\mcpe\tile\Cauldron as Tile;

use pocketmine\block\Transparent;

use pocketmine\item\{
    Item,
    TieredTool,
    Armor,
    Potion
};

use pocketmine\block\{
    BlockToolType,
    Block
};

use pocketmine\math\Vector3;

use pocketmine\Player;

use pocketmine\network\mcpe\protocol\LevelEventPacket;

use pocketmine\utils\Color;

use pocketmine\nbt\tag\IntTag;

class Cauldron extends Transparent {
    protected $id = self::CAULDRON_BLOCK;
    protected $itemId = Item::CAULDRON;

    public function __construct($meta = 0) {
        $this->meta = $meta;
    }

	public function getName() : string {
		return "Cauldron";
	}

    public function getHardness() : float {
        return 2;
    }

    public function getToolType() : int {
        return BlockToolType::TYPE_PICKAXE;
    }

    public function getToolHarvestLevel() : int {
        return TieredTool::TIER_WOODEN;
    }

    public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = \null): bool{
        Tile::createTile(Tile::CAULDRON, $this->getLevel(), Tile::createNBT($this, $face, $item, $player));

        return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

	public function canBeActivated() : bool {
		return true;
	}

    public function onActivate(Item $item, Player $player = null) : bool {
        $tile = $this->getLevel()->getTile($this);

        if(!$tile instanceof Tile) {
            return false;
        }
        switch($item->getId()) {
            case Item::BUCKET:
                if($item->getDamage() == 0) {
                    if(!$this->isFull() or $tile->hasCustomColor() or $tile->hasPotion()) {
                        break;
                    }
                    $bucket = clone $item;

                    $bucket->setDamage(8);

                    if($player->isSurvival()) {
                        $player->getInventory()->setItemInHand($bucket);
                    }
                    $this->meta = 0;

                    $tile->resetCustomColor();
                    $this->getLevel()->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_TAKE_WATER);
                } else if($item->getDamage() == 8) {
                    if($this->isFull() and !$tile->hasCustomColor() and !$tile->hasPotion()) {
                        break;
                    }
                    $bucket = clone $item;

                    $bucket->setDamage(0);

                    if($player->isSurvival()) {
                        $player->getInventory()->setItemInHand($bucket);
                    }
                    if($tile->hasPotion()) {
                        $tile->resetPotion();
                        $tile->setSplashPotion(false);
                        $tile->resetCustomColor();

                        $this->meta = 0;

                        $this->getLevel()->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_EXPLODE);
                    } else {
                        $this->meta = 6;

                        $tile->resetCustomColor();
                        $this->getLevel()->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_FILL_WATER);
                    }
                }
            break;
            case Item::DYE:
                if($tile->hasPotion()) {
                    break;
                }
                $col = \core\utils\Item::getDyeColor($item->getDamage());

                $col->setA(127);

                if($tile->hasCustomColor()) {
                    $color = Color::mix($tile->getCustomColor(), $col);
                } else {
                    $color = $col;
                }
                if($player->isSurvival()) {
                    $item->pop();
                }
                $tile->setCustomColor($color);
                $this->getLevel()->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_ADD_DYE);
            break;
            case Item::LEATHER_CAP:
            case Item::LEATHER_TUNIC:
            case Item::LEATHER_PANTS:
            case Item::LEATHER_BOOTS:
                if($this->isEmpty() || $tile->hasPotion()) break;
                if($tile->hasCustomColor()) {
                    --$this->meta;
                    /** @var Armor $newItem */
                    $newItem = clone $item;

                    $newItem->setCustomColor($tile->getCustomColor());
                    $player->getInventory()->setItemInHand($newItem);
                    $this->getLevel()->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_DYE_ARMOR);

                    if($this->isEmpty()) {
                        $tile->resetCustomColor();
                    }
                } else {
                    --$this->meta;
                    /** @var Armor $newItem */
                    $newItem = clone $item;

                    if($newItem->getNamedTag()->hasTag(Armor::TAG_CUSTOM_COLOR, IntTag::class)) {
                        $newItem->getNamedTag()->removeTag(Armor::TAG_CUSTOM_COLOR);
                    }
                    $player->getInventory()->setItemInHand($newItem);
                    $this->getLevel()->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_CLEAN_ARMOR);
                }
            break;
            case Item::POTION:
            case Item::SPLASH_POTION:
                if(!$this->isEmpty() && (($tile->getPotionId() != $item->getDamage() && $item->getDamage() != 0) || ($item->getId() == Item::POTION && $tile->isSplashPotion()) || ($item->getId() == Item::SPLASH_POTION && !$tile->isSplashPotion()) && $item->getDamage() != 0 || ($item->getDamage() == 0 && $tile->hasPotion()))){
                    $this->meta = 0;

                    $tile->resetPotion();
                    $tile->setSplashPotion(false);
                    $tile->resetCustomColor();

                    if($player->isSurvival()) {
                        $player->getInventory()->setItemInHand(Item::get(Item::GLASS_BOTTLE));
                    }
                    $this->getLevel()->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_EXPLODE);
                } else if($item->getDamage() === 0) {
                    $this->meta += 2;

                    if($this->meta > 6) {
                        $this->meta = 6;
                    }
                    if($player->isSurvival()) {
                        $player->getInventory()->setItemInHand(Item::get(Item::GLASS_BOTTLE));
                    }
                    $tile->resetPotion();
                    $tile->setSplashPotion(false);
                    $tile->resetCustomColor();
                    $this->getLevel()->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_FILL_WATER);
                } else if(!$this->isFull()) {
                    $this->meta += 2;

                    if($this->meta > 6) {
                        $this->meta = 6;
                    }
                    $tile->setPotionId($item->getDamage());
                    $tile->setSplashPotion($item->getId() == Item::SPLASH_POTION);

                    $col = new Color(0, 0, 0, 0);

                    foreach(Potion::getPotionEffectsById($item->getDamage()) as $effect){
                        $col = Color::mix($effect->getColor(), $col);
                    }
                    $tile->setCustomColor($col);

                    if($player->isSurvival()){
                        $player->getInventory()->setItemInHand(Item::get(Item::GLASS_BOTTLE));
                    }
                    $this->getLevel()->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_TAKE_POTION);
                }
            break;
            case Item::GLASS_BOTTLE:
                if($this->meta < 2) {
                    break;
                }
                if($tile->hasPotion()) {
                    $this->meta -= 2;

                    if($tile->isSplashPotion()) {
                        $result = Item::get(Item::SPLASH_POTION, $tile->getPotionId());
                    } else {
                        $result = Item::get(Item::POTION, $tile->getPotionId());
                    }
                    if($this->isEmpty()) {
                        $tile->resetPotion();
                        $tile->setSplashPotion(false);
                        $tile->resetCustomColor();
                    }
                    $item->pop();

                    if(($inv = $player->getInventory())->canAddItem($result)) {
                        $inv->addItem($result);
                    } else {
                        $this->getLevel()->dropItem($player, $result);
                    }
                    $this->getLevel()->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_TAKE_POTION);
                } else {
                    $this->meta -= 2;

                    if($player->isSurvival()) {
                        $result = Item::get(Item::POTION, 0);

                        $item->pop();

                        if(($inv = $player->getInventory())->canAddItem($result)) {
                            $inv->addItem($result);
                        } else {
                            $this->getLevel()->dropItem($player, $result);
                        }
                    }
                    $this->getLevel()->broadcastLevelEvent($this, LevelEventPacket::EVENT_CAULDRON_TAKE_WATER);
                }
            break;
        }
        $this->meta += 3;

        $this->getLevel()->setBlock($this, $this, true);

        $this->meta -= 3;
        $this->getLevel()->setBlock($this, $this, true);
        return true;
    }

    public function isFull() : bool {
        return $this->meta >= 6;
    }

    public function isEmpty() : bool {
        return $this->meta == 0;
    }
}