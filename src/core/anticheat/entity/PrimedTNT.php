<?php

namespace core\anticheat\entity;

use core\Core;

use pocketmine\event\entity\ExplosionPrimeEvent;

use pocketmine\level\Explosion;

class PrimedTNT extends \pocketmine\entity\object\PrimedTNT {
    public function explode() : void {
		$this->server->getPluginManager()->callEvent($event = new ExplosionPrimeEvent($this, 4));
		
        if(!$event->isCancelled()) {
			Core::getInstance()->getAntiCheat()->addToExplosionQueue(new Explosion($event->getEntity(), $event->getForce(), $event->getEntity()));
		}
    }
}