<?php

declare(strict_types = 1);

namespace core\mcpe\entity\projectile;

use core\CorePlayer;

use pocketmine\Server;

use pocketmine\entity\projectile\Projectile;

use pocketmine\block\{
    Water,
    StillWater
};

use pocketmine\network\mcpe\protocol\ActorEventPacket;

use pocketmine\entity\Entity;

use pocketmine\math\RayTraceResult;

use pocketmine\event\entity\{
    ProjectileHitEntityEvent,
    EntityDamageEvent,
    EntityDamageByEntityEvent,
    EntityDamageByChildEntityEvent
};

class FishingHook extends Projectile {
    public const NETWORK_ID = self::FISHING_HOOK;

    public $width = 0.25;
    public $length = 0.25;
    public $height = 0.25;
    public $coughtTimer = 0;
    public $attractTimer = 0;

    protected $gravity = 0.1;
    protected $drag = 0.05;
    protected $touchedWater = false;

    public function onUpdate(int $currentTick) : bool {
        if($this->isFlaggedForDespawn() or !$this->isAlive()) {
            return false;
        }
        $this->timings->startTiming();

        $hasUpdate = parent::onUpdate($currentTick);

        if($this->isCollidedVertically) {
            $this->motion->x = 0;
            $this->motion->y += 0.01;
            $this->motion->z = 0;
            $hasUpdate = true;
        } else if($this->isCollided && $this->keepMovement === true) {
            $this->motion->x = 0;
            $this->motion->y = 0;
            $this->motion->z = 0;
            $this->keepMovement = false;
            $hasUpdate = true;
        }
        if($this->isCollided && !$this->touchedWater) {
            foreach($this->getBlocksAround() as $block) {
                if($block instanceof Water or $block instanceof StillWater) {
                    $this->touchedWater = true;
                    $pk = new ActorEventPacket();
                    $pk->entityRuntimeId = $this->getId();
                    $pk->event = ActorEventPacket::FISH_HOOK_POSITION;

                    Server::getInstance()->broadcastPacket($this->getViewers(), $pk);
                    break;
                }
            }
        }
        if($this->attractTimer === 0 && mt_rand(0, 100) <= 30) {
            $this->coughtTimer = mt_rand(5, 10) * 20;
            $this->attractTimer = mt_rand(30, 100) * 20;

            $this->attractFish();

            $owningEntity = $this->getOwningEntity();

            if($owningEntity instanceof CorePlayer) {
                $owningEntity->sendTip("A fish bites!");
            }
        } else if($this->attractTimer > 0) {
            $this->attractTimer--;
        }
        if($this->coughtTimer > 0) {
            $this->coughtTimer--;
            $this->fishBites();
        }
        $this->timings->stopTiming();
        return $hasUpdate;
    }

    public function attractFish() {
        $owningEntity = $this->getOwningEntity();

        if($owningEntity instanceof CorePlayer) {
            $pk = new ActorEventPacket();
            $pk->entityRuntimeId = $this->getId();
            $pk->event = ActorEventPacket::FISH_HOOK_BUBBLE;

            Server::getInstance()->broadcastPacket($this->getViewers(), $pk);
        }
    }

    public function fishBites() {
        $owningEntity = $this->getOwningEntity();

        if($owningEntity instanceof CorePlayer) {
            $pk = new ActorEventPacket();
            $pk->entityRuntimeId = $this->getId();
            $pk->event = ActorEventPacket::FISH_HOOK_HOOK;

            Server::getInstance()->broadcastPacket($this->getViewers(), $pk);
        }
    }

    public function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void {
        $this->server->getPluginManager()->callEvent(new ProjectileHitEntityEvent($this, $hitResult, $entityHit));

        $damage = $this->getResultDamage();

        if($this->getOwningEntity() === null) {
            $event = new EntityDamageByEntityEvent($this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
        } else {
            $event = new EntityDamageByChildEntityEvent($this->getOwningEntity(), $this, $entityHit, EntityDamageEvent::CAUSE_PROJECTILE, $damage);
        }
        $entityHit->attack($event);
        $entityHit->setMotion($this->getOwningEntity()->getDirectionVector()->multiply(-0.3)->add(0, 0.3, 0));

        $this->isCollided = true;

        $this->flagForDespawn();
    }

    public function getResultDamage() : int {
        return 1;
    }
}