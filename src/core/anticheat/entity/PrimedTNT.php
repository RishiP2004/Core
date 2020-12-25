<?php

declare(strict_types = 1);

namespace core\anticheat\entity;

use core\anticheat\AntiCheat;

use pocketmine\event\entity\ExplosionPrimeEvent;

use pocketmine\level\Explosion;

class PrimedTNT extends \pocketmine\entity\object\PrimedTNT {
    public function explode() : void {
		$this->server->getPluginManager()->callEvent($event = new ExplosionPrimeEvent($this, 4));
		
        if(!$event->isCancelled()) {
			AntiCheat::getInstance()->addToExplosionQueue(new Explosion($event->getEntity(), $event->getForce(), $event->getEntity()));
		}
    }
}