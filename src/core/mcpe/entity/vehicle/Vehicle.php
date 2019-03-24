<?php

namespace core\mcpe\entity\vehicle;

use core\CorePlayer;

use core\utils\Math;

use pocketmine\entity\Entity;

use pocketmine\level\Level;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\event\entity\{
    EntityDamageEvent,
    EntityDamageByEntityEvent
};

use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\types\EntityLink;

use pocketmine\network\mcpe\protocol\SetEntityLinkPacket;

abstract class Vehicle extends \pocketmine\entity\Vehicle {
    /** @var Entity */
    protected $linkedEntity = null;

    protected $canInteract = false;

    protected $rollingDirection = true;

    public function __construct(Level $level, CompoundTag $nbt) {
        parent::__construct($level, $nbt);
    }

    public function getRollingAmplitude() : int {
        return $this->propertyManager->getInt(self::DATA_HURT_TIME);
    }

    public function setRollingAmplitude(int $time) {
        $this->propertyManager->setInt(self::DATA_HURT_TIME, $time);
    }

    public function getDamage() : int {
        return $this->propertyManager->getInt(self::DATA_HEALTH);
    }

    public function getRollingDirection() : int {
        return $this->propertyManager->getInt(self::DATA_HURT_DIRECTION);
    }

    public function setRollingDirection(int $direction) {
        $this->propertyManager->setInt(self::DATA_HURT_DIRECTION, $direction);
    }

    public function setDamage(int $damage) {
        if($damage > 40 || $damage < -20) {
            $damage = 40;
        }
        $this->propertyManager->setInt(self::DATA_HEALTH, $damage);
    }

    public function getInteractButtonText() : string {
        return "Mount";
    }

    public function getLinkedEntity() : ?Entity {
        return $this->linkedEntity;
    }

    public function canDoInteraction() {
        return $this->linkedEntity === null && $this->canInteract;
    }

    public function initEntity() : void {
        parent::initEntity();
        $this->setRollingAmplitude(0);
        $this->setDamage(0);
        $this->setRollingDirection(0);

        $this->y += $this->baseOffset;
    }

    public function attack(EntityDamageEvent $source) : void {
        $damage = null;
        $instantKill = false;

        if($source instanceof EntityDamageByEntityEvent) {
            $damage = $source->getDamager();
            $instantKill = $damage instanceof CorePlayer && $damage->isCreative();
        }
        if(!$instantKill) $this->performHurtAnimation(rand(4, 8));

        if($instantKill or $this->getDamage() <= 0) {
            if($this->linkedEntity !== null) {
                $this->mountEntity($this->linkedEntity);
            }
            if($instantKill) {
                $this->kill();
            } else {
                $this->close();
            }
        }
    }

    public function mountEntity(Entity $entity) : bool {
        if(is_null($entity)) {
            return false;
        }
        $riding = new EntityLink();

        if(isset($entity->riding) && !is_null($entity->riding)) {
            $pk = new SetEntityLinkPacket();
            $riding->fromEntityUniqueId = $this->getId();
            $riding->toEntityUniqueId = $entity->getId();
            $riding->type = EntityLink::TYPE_REMOVE;
            $pk->link = $riding;

            $this->server->broadcastPacket($this->hasSpawned, $pk);

            if($entity instanceof CorePlayer) {
                $pk = new SetEntityLinkPacket();
                $riding->fromEntityUniqueId = $this->getId();
                $riding->toEntityUniqueId = $entity->getId();
                $riding->type = EntityLink::TYPE_REMOVE;
                $pk->link = $riding;

                $entity->dataPacket($pk);
            }
            $entity->riding = null;
            $this->linkedEntity = null;

            $entity->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_RIDING, false);
            return true;
        }
        $pk = new SetEntityLinkPacket();
        $riding->fromEntityUniqueId = $this->getId();
        $riding->toEntityUniqueId = $entity->getId();
        $riding->type = EntityLink::TYPE_PASSENGER;
        $pk->link = $riding;

        $this->server->broadcastPacket($this->hasSpawned, $pk);

        if($entity instanceof CorePlayer) {
            $pk = new SetEntityLinkPacket();
            $riding->fromEntityUniqueId = $this->getId();
            $riding->toEntityUniqueId = 0;
            $riding->type = EntityLink::TYPE_PASSENGER;
            $pk->link = $riding;

            $entity->dataPacket($pk);
        }
        $entity->riding = $this;
        $this->linkedEntity = $entity;

        $entity->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_RIDING, true);
        $this->propertyManager->setVector3(self::DATA_RIDER_SEAT_POSITION, new Vector3(0, $this->baseOffset * 2, 0));
        return true;
    }

    public function onUpdate(int $currentTick) : bool {
        $hasUpdated = parent::onUpdate($currentTick);

        if($this->closed or !$this->isAlive()){
            return false;
        }
        if($this->getRollingAmplitude() > 0) {
            $this->setRollingAmplitude($this->getRollingAmplitude() - 1);
            $hasUpdated = true;
        }
        if($this->getDamage() >= -10 && $this->getDamage() <= 40) {
            $this->setDamage($this->getDamage() + 1);
            $hasUpdated = true;
        }

        return $hasUpdated;
    }

    protected function performHurtAnimation(float $damage){
        $this->setRollingAmplitude(10);
        $this->setRollingDirection($this->rollingDirection ? 1 : -1);
        $this->rollingDirection = !$this->rollingDirection;
        $this->setDamage($this->getDamage() - $damage);
        return true;
    }

    public function applyEntityCollision(Entity $to) {
        if((!isset($to->riding) or $to->riding !== $this) && (!isset($to->linkedEntity) or $to->linkedEntity != $this)) {
            $dx = $this->x - $to->x;
            $dy = $this->z - $to->z;
            $dz = Math::getDirection($dx, $dy);

            if($dz >= 0.01) {
                $dz = sqrt($dz);
                $dx /= $dz;
                $dy /= $dz;
                $d3 = 1 / $dz;

                if($d3 > 1) {
                    $d3 = 1;
                }
                $dx *= $d3;
                $dy *= $d3;
                $dx *= 0.05;
                $dy *= 0.05;

                if(!isset($to->riding) or $to->riding !== null) {
                    $this->motion->x -= $dx;
                    $this->motion->z -= $dz;
                }
            }
        }
    }
}