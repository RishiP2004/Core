<?php

namespace core\mcpe\entity\object;

use pocketmine\entity\Entity;

use pocketmine\nbt\tag\{
    ByteTag,
    ListTag,
    DoubleTag
};

use pocketmine\math\Vector3;

use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\level\Explosion;

class EnderCrystal extends Entity {
    public const NETWORK_ID = self::ENDER_CRYSTAL;

	public const TAG_SHOW_BOTTOM = "ShowBottom";

    public $height = 0.98, $width = 0.98;

    public function initEntity() : void {
        if(!$this->namedtag->hasTag(self::TAG_SHOW_BOTTOM, ByteTag::class)) {
            $this->namedtag->setByte(self::TAG_SHOW_BOTTOM, 0);
        }
        parent::initEntity();
    }

    public function isShowingBottom() : bool {
        return boolval($this->namedtag->getByte(self::TAG_SHOW_BOTTOM));
    }

    public function setShowingBottom(bool $value) {
        $this->namedtag->setByte(self::TAG_SHOW_BOTTOM, intval($value));
    }

    public function setBeamTarget(Vector3 $pos) {
        $this->namedtag->setTag(new ListTag("BeamTarget", [
            new DoubleTag("", $pos->getX()),
            new DoubleTag("", $pos->getY()),
            new DoubleTag("", $pos->getZ()),
        ]));
    }

    public function attack(EntityDamageEvent $source): void{
        if(!$this->isFlaggedForDespawn()) {
            $pos = clone $this->asPosition();

            $this->flagForDespawn();

            $explode = new Explosion($pos, 6, $this);

            $explode->explodeA();
            $explode->explodeB();
        }
    }
}