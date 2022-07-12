<?php

declare(strict_types = 1);

namespace core\anticheat\entity;

use core\anticheat\AntiCheatManager;

use pocketmine\event\entity\ExplosionPrimeEvent;

use pocketmine\world\Explosion;

class PrimedTNT extends \pocketmine\entity\object\PrimedTNT {
    public function explode() : void {
    	$event = new ExplosionPrimeEvent($this, 4);
    	$event->call();

        if(!$event->isCancelled()) {
			AntiCheatManager::getInstance()->addToExplosionQueue(new Explosion($event->getEntity()->getPosition(), $event->getForce(), $event->getEntity()));
		}
    }
}