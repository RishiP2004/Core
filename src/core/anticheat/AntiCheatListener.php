<?php

declare(strict_types = 1);

namespace core\anticheat;

use core\CorePlayer;

use core\anticheat\cheat\AutoClicker;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\event\entity\{
	EntitySpawnEvent,
	EntityDespawnEvent
};

use pocketmine\entity\{
	Human,
	Animal,
	Monster
};

class AntiCheatListener implements Listener {
	private $manager;

	public function __construct(AntiCheat $manager) {
		$this->manager = $manager;
	}

	public function onPlayerInteract(PlayerInteractEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$autoClicker = $this->manager->getCheat(AutoClicker::AUTO_CLICKER);

			$autoClicker->set($player);
			$autoClicker->onRun();
		}
	}

	public function onEntitySpawn(EntitySpawnEvent $event) {
		$entity = $event->getEntity();

		if($entity instanceof Human) {
			return;
		}
		$despawn = null;
		$uuid = uniqid();

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

	public function onEntityDespawn(EntityDespawnEvent $event) {
		$entity = $event->getEntity();

		if(!isset($this->manager->ids[$entity->getId()])) {
			return;
		}
		$uuid = $this->manager->ids[$entity->getId()];

		unset($this->manager->ids[$entity->getId()]);

		if(isset($this->amanager->nimals[$uuid])) {
			unset($this->manager->animals[$uuid]);
			return;
		}
		if(isset($this->manager->monsters[$uuid])) {
			unset($this->manager->monsters[$uuid]);
			return;
		}
		if(isset($this->manager->itemEntities[$uuid])) {
			unset($this->manager->itemEntities[$uuid]);
			return;
		}
	}
}