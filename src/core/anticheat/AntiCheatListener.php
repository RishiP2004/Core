<?php

declare(strict_types = 1);

namespace core\anticheat;

use core\player\CorePlayer;

use core\anticheat\cheat\Cheat;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\event\entity\{
	EntitySpawnEvent,
	EntityDespawnEvent
};
use pocketmine\entity\{
	Entity,
	Human
};

use pocketmine\block\FenceGate;

class AntiCheatListener implements Listener {
	public function __construct(private AntiCheatManager $manager) {}

	public function onPlayerInteract(PlayerInteractEvent $event) : void {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$autoClicker = $this->manager->getCheat(Cheat::AUTO_CLICKER);

			$autoClicker->set($player);
			$autoClicker->onRun();

			$block = $event->getBlock();

			if ($block instanceof FenceGate) {
				$antiGlitch = $this->manager->getCheat(Cheat::GLITCH);

				$antiGlitch->set($player);
				$antiGlitch->onRun();
			}
		}
	}

	public function onEntitySpawn(EntitySpawnEvent $event) : void {
		$entity = $event->getEntity();

		if($entity instanceof Human) {
			return;
		}
		$despawn = null;
		$uuid = uniqid();
		//Wait for PMMP to re-implement?
		/*
		if($entity instanceof Animal) {
			$this->manager->ids[$entity->getId()] = $uuid;
			$this->manager->animals[$uuid] = $entity;

			if(count($this->manager->animals) > $this->manager::MAX_ENTITIES["animals"]) {
				$despawn = array_shift($this->manager->animals);
			}
		}
		if($entity instanceof Monster) {
			$this->manager->ids[$entity->getId()] = $uuid;
			$this->manager->monsters[$uuid] = $entity;

			if(count($this->manager->monsters) > $this->manager::MAX_ENTITIES["monsters"]) {
				$despawn = array_shift($this->manager->monsters);
			}
		}*/
		if($entity instanceof Entity) {
			$this->manager->ids[$entity->getId()] = $uuid;
			$this->manager->entities[$uuid] = $entity;

			if(count($this->manager->entities) > $this->manager::MAX_ENTITIES["entities"]) {
				$despawn = array_shift($this->manager->entities);
			}
		}
		if($entity instanceof \pocketmine\entity\object\ItemEntity) {
			$this->manager->ids[$entity->getId()] = $uuid;
			$this->manager->itemEntities[$uuid] = $entity;

			if(count($this->manager->itemEntities) > $this->manager::MAX_ENTITIES["items"]) {
				$despawn = array_shift($this->manager->itemEntities);
			}
		}
		if($despawn === null) {
			return;
		}
		if($despawn->isClosed()) {
			return;
		}
		$despawn->flagForDespawn();
	}

	public function onEntityDespawn(EntityDespawnEvent $event) : void {
		$entity = $event->getEntity();

		if(!isset($this->manager->ids[$entity->getId()])) {
			return;
		}
		$uuid = $this->manager->ids[$entity->getId()];

		unset($this->manager->ids[$entity->getId()]);
		//Wait for PMMP to re-implement?
		/**
		if(isset($this->manager->animals[$uuid])) {
			unset($this->manager->animals[$uuid]);
			return;
		}
		if(isset($this->manager->monsters[$uuid])) {
			unset($this->manager->monsters[$uuid]);
			return;
		}*/
		if(isset($this->manager->entities[$uuid])) {
			unset($this->manager->entities[$uuid]);
			return;
		}
		if(isset($this->manager->itemEntities[$uuid])) {
			unset($this->manager->itemEntities[$uuid]);
		}
	}
}