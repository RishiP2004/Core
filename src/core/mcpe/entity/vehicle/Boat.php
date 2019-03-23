<?php

namespace core\mcpe\entity\vehicle;

use pocketmine\entity\Entity;

use pocketmine\nbt\tag\ByteTag;

use pocketmine\item\Item;

use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\network\mcpe\protocol\EntityEventPacket;

use pocketmine\Server;

class Boat extends Vehicle {
    public const TAG_WOOD_ID = "WoodID";

    public const NETWORK_ID = self::BOAT;

    public $height = 0.7;
    public $width = 1.6;
    public $gravity = 0;
    public $drag = 0.1;

    /** @var Entity */
    public $linkedEntity = null;

    protected $age = 0;

    public function initEntity() : void {
        if(!$this->namedtag->hasTag(self::TAG_WOOD_ID, ByteTag::class)) {
            $this->namedtag->setByte(self::TAG_WOOD_ID, 0);
        }
        $this->setMaxHealth(4);

        parent::initEntity();
    }

    public function getDrops() : array {
        return [
            Item::get(Item::BOAT, $this->getWoodID(), 1),
        ];
    }

    public function getWoodID() {
        return $this->namedtag->getByte(self::TAG_WOOD_ID);
    }

    public function attack(EntityDamageEvent $source) : void {
        parent::attack($source);

        if(!$source->isCancelled()) {
            $pk = new EntityEventPacket();
            $pk->entityRuntimeId = $this->id;
            $pk->event = EntityEventPacket::HURT_ANIMATION;

            Server::getInstance()->broadcastPacket($this->getViewers(), $pk);
        }
    }

    public function entityBaseTick(int $tickDiff = 1) : bool {
		return false;
		//TODO
    }
}