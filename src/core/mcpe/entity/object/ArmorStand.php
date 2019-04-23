<?php

declare(strict_types = 1);

namespace core\mcpe\entity\object;

use pocketmine\Player;

use pocketmine\item\Item;

use pocketmine\entity\Entity;

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ListTag;

use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\{
    MobEquipmentPacket,
    MobArmorEquipmentPacket,
    LevelEventPacket
};

use pocketmine\event\entity\{
    EntityDamageEvent,
    EntityDamageByEntityEvent
};

class ArmorStand extends Entity {
    public const NETWORK_ID = self::ARMOR_STAND;
    public const TAG_HAND_ITEMS = "HandItems";
    public const TAG_ARMOR_ITEMS = "ArmorItems";

    public $height = 1.975, $width = 0.5;

    protected $gravity = 0.04;
    /** @var Item */
    protected $itemInHand, $itemOffHand, $helmet, $chestplate, $leggings, $boots;

    public function initEntity() : void {
        $air = Item::get(Item::AIR)->nbtSerialize();

        if(!$this->namedtag->hasTag(self::TAG_HAND_ITEMS, ListTag::class)) {
            $this->namedtag->setTag(new ListTag(self::TAG_HAND_ITEMS, [
                $air,
                $air
            ], NBT::TAG_Compound));
        }
        if(!$this->namedtag->hasTag(self::TAG_ARMOR_ITEMS, ListTag::class)) {
            $this->namedtag->setTag(new ListTag(self::TAG_ARMOR_ITEMS, [
                $air,
                $air,
                $air,
                $air
            ], NBT::TAG_Compound));
        }
        $handItems = $this->namedtag->getListTag(self::TAG_HAND_ITEMS);
        $armorItems = $this->namedtag->getListTag(self::TAG_ARMOR_ITEMS);
        $this->itemInHand = Item::nbtDeserialize($handItems[0]);
        $this->itemOffHand = Item::nbtDeserialize($handItems[1]);
        $this->helmet = Item::nbtDeserialize($armorItems[3]);
        $this->chestplate = Item::nbtDeserialize($armorItems[2]);
        $this->leggings = Item::nbtDeserialize($armorItems[1]);
        $this->boots = Item::nbtDeserialize($armorItems[0]);

        $this->setHealth(6);
        $this->setMaxHealth(6);
        parent::initEntity();
    }

    public function canCollideWith(Entity $entity) : bool {
        return false;
    }

    public function onInteract(Player $player, Item $item, int $slot, Vector3 $clickPos) : bool {
        if(!$player->isSneaking()) {
            $diff = $clickPos->getY() - $this->getY();
            $type = Item::getType($item);
            $playerInv = $player->getInventory();

            switch(true) {
                case ($diff < 0.5):
                    $clicked = Item::TYPE_BOOTS;
                break;
                case ($diff < 1):
                    $clicked = Item::TYPE_LEGGINGS;
                break;
                case ($diff < 1.5):
                    $clicked = Item::TYPE_CHESTPLATE;
                break;
                default:
                    $clicked = Item::TYPE_HELMET;
                break;
            }
            if($item->isNull()) {
                if($clicked === Item::TYPE_CHESTPLATE) {
                    if($this->getItemInHand()->isNull()) {
                        $ASchestplate = clone $this->getChestplate();

                        $this->setChestplate($item);
                        $playerInv->setItemInHand(Item::get(Item::AIR));
                        $playerInv->addItem($ASchestplate);
                    } else {
                        $ASiteminhand = clone $this->getItemInHand();

                        $this->setItemInHand($item);
                        $playerInv->setItemInHand(Item::get(Item::AIR));
                        $playerInv->addItem($ASiteminhand);
                    }
                } else {
                    $old = clone $this->get($clicked);

                    $this->set($clicked, $item);
                    $playerInv->setItemInHand(Item::get(Item::AIR));
                    $playerInv->addItem($old);
                }
            } else {
                if($type === Item::TYPE_NULL) {
                    if($this->getItemInHand()->equals($item)) {
                        $playerInv->addItem(clone $this->getItemInHand());
                        $this->setItemInHand(Item::get(Item::AIR));
                    } else {
                        $playerInv->addItem(clone $this->getItemInHand());

                        $ic = clone $item;
                        $ic->count--;
                        $this->setItemInHand((clone $ic)->setCount(1));
                        $playerInv->setItemInHand($ic);
                    }
                } else {
                    $old = clone $this->get($type);

                    $this->set($type, $item);
                    $playerInv->setItemInHand(Item::get(Item::AIR));
                    $playerInv->addItem($old);
                }
            }
            $this->sendAll();
        }
        return true;
    }

    public function getItemInHand() : Item {
        return $this->itemInHand;
    }

    public function setItemInHand(Item $item) {
        $this->itemInHand = $item;

        $this->sendAll();
    }

    public function getHelmet() : Item {
        return $this->helmet;
    }

    public function setHelmet(Item $item) {
        $this->helmet = $item;
        $this->sendAll();
    }

    public function getChestplate() : Item {
        return $this->chestplate;
    }

    public function setChestplate(Item $item) {
        $this->chestplate = $item;

        $this->sendAll();
    }

    public function getLeggings() : Item {
        return $this->leggings;
    }

    public function setLeggings(Item $item) {
        $this->leggings = $item;
        $this->sendAll();
    }

    public function getBoots() : Item {
        return $this->boots;
    }

    public function setBoots(Item $item) {
        $this->boots = $item;
        $this->sendAll();
    }

    private function get(string $armorType) : Item {
        switch($armorType) {
            case Item::TYPE_HELMET:
                return $this->getHelmet();
            case Item::TYPE_CHESTPLATE:
                return $this->getChestplate();
            case Item::TYPE_LEGGINGS:
                return $this->getLeggings();
            case Item::TYPE_BOOTS:
                return $this->getBoots();
            case "INHAND":
                return $this->getItemInHand();
            case "OFFHAND":
                return $this->getItemOffHand();
        }
        return \pocketmine\item\Item::get(Item::AIR);
    }

    public function getItemOffHand() : Item {
        return $this->itemOffHand;
    }

    public function setItemOffHand(Item $item) {
        $this->itemOffHand = $item;

        $this->sendAll();
    }

    private function set(string $armorType, Item $item) {
        switch($armorType) {
            case Item::TYPE_HELMET:
                $this->setHelmet($item);
            break;
            case Item::TYPE_CHESTPLATE:
                $this->setChestplate($item);
            break;
            case Item::TYPE_LEGGINGS:
                $this->setLeggings($item);
            break;
            case Item::TYPE_BOOTS:
                $this->setBoots($item);
            break;
            case "INHAND":
                $this->setItemInHand($item);
            break;
            case "OFFHAND":
                $this->setItemOffHand($item);
             break;
        }
    }

    public function sendAll() {
        foreach($this->getViewers() as $player) {
            $this->sendHandItems($player);
            $this->sendArmorItems($player);
        }
    }

    public function sendHandItems(Player $player) {
        $pk = new MobEquipmentPacket();
        $pk->entityRuntimeId = $this->getId();
        $pk->inventorySlot = $pk->hotbarSlot = 0;
        $pk->item = $this->getItemInHand();

        $player->dataPacket($pk);

        $pk = new MobEquipmentPacket();
        $pk->entityRuntimeId = $this->getId();
        $pk->inventorySlot = $pk->hotbarSlot = 1;
        $pk->item = $this->getItemOffHand();

        $player->dataPacket($pk);
    }

    public function sendArmorItems(Player $player){
        $pk = new MobArmorEquipmentPacket();
        $pk->entityRuntimeId = $this->getId();
        $pk->slots = [$this->getHelmet(), $this->getChestplate(), $this->getLeggings(), $this->getBoots()];

        $player->dataPacket($pk);
    }

    public function kill() : void {
        $this->level->dropItem($this, Item::get(Item::ARMOR_STAND));
        $this->level->dropItem($this, $this->getItemInHand());
        $this->level->dropItem($this, $this->getItemOffHand());
        $this->level->dropItem($this, $this->getHelmet());
        $this->level->dropItem($this, $this->getChestplate());
        $this->level->dropItem($this, $this->getLeggings());
        $this->level->dropItem($this, $this->getBoots());
        parent::kill();
    }

    public function spawnTo(Player $player) : void {
        parent::spawnTo($player);
        $this->sendArmorItems($player);
        $this->sendHandItems($player);
    }

    public function saveNBT() : void {
        parent::saveNBT();

        $this->namedtag->setTag(new ListTag(self::TAG_ARMOR_ITEMS, [
            $this->boots->nbtSerialize(),
            $this->leggings->nbtSerialize(),
            $this->chestplate->nbtSerialize(),
            $this->helmet->nbtSerialize(),
        ], NBT::TAG_Compound));
        $this->namedtag->setTag(new ListTag(self::TAG_HAND_ITEMS, [
            $this->getItemInHand()->nbtSerialize(),
            $this->getItemOffHand()->nbtSerialize(),
        ], NBT::TAG_Compound));
    }

    public function applyGravity() : void {
        $this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ARMOR_STAND_FALL);
        parent::applyGravity();
    }

    public function attack(EntityDamageEvent $source) : void {
        if($source instanceof EntityDamageByEntityEvent) {
            $damager = $source->getDamager();

            if($damager instanceof Player) {
                if($damager->isCreative()) {
                    $this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ARMOR_STAND_BREAK);
                    $this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_PARTICLE_DESTROY, 5);
                    $this->flagForDespawn();
                } else {
                    $this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ARMOR_STAND_HIT);
                }
            }
        }
        if($source->getCause() != EntityDamageEvent::CAUSE_CONTACT) {
            Entity::attack($source);
        }
    }
}