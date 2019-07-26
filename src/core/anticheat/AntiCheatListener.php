<?php

declare(strict_types = 1);

namespace core\anticheat;

use core\broadcast\Broadcasts;
use core\Core;
use core\CorePlayer;

use core\anticheat\cheat\AutoClicker;

use core\mcpe\entity\{
	AnimalBase,
	MonsterBase
};
use core\mcpe\entity\object\ItemEntity;

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

class AntiCheatListener implements Listener, Cheats {
	private $core;

	public function __construct(Core $core) {
		$this->core = $core;
	}

	public function onPlayerInteract(PlayerInteractEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$autoClicker = $this->core->getAntiCheat()->getCheat(AutoClicker::AUTO_CLICKER);

			$autoClicker->set($player);
			$autoClicker->onRun();
		}
	}

	public function onEntitySpawn(EntitySpawnEvent $event) {
		$entity = $event->getEntity();
		$data = $this->core->getAntiCheat();

		if($entity instanceof Human) {
			return;
		}
		$despawn = null;
		$uuid = uniqid();

		if($entity instanceof AnimalBase or $entity instanceof Animal) {
			$data->ids[$entity->getId()] = $uuid;
			$data->animals[$uuid] = $entity;

			if(count($data->animals) > self::MAX_ENTITIES["animals"]) {
				$despawn = array_shift($data->animals);
			}
		}
		if($entity instanceof MonsterBase or $entity instanceof Monster) {
			$data->ids[$entity->getId()] = $uuid;
			$data->monsters[$uuid] = $entity;

			if(count($data->monsters) > self::MAX_ENTITIES["monsters"]) {
				$despawn = array_shift($data->monsters);
			}
		}
		if($entity instanceof ItemEntity or $entity instanceof \pocketmine\entity\object\ItemEntity) {
			$data->ids[$entity->getId()] = $uuid;
			$data->itemEntities[$uuid] = $entity;

			if(count($data->itemEntities) > self::MAX_ENTITIES["items"]) {
				$despawn = array_shift($data->itemEntities);
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
		$data = $this->core->getAntiCheat();

		if(!isset($data->ids[$entity->getId()])) {
			return;
		}
		$uuid = $data->ids[$entity->getId()];

		unset($data->ids[$entity->getId()]);

		if(isset($data->animals[$uuid])) {
			unset($data->animals[$uuid]);
			return;
		}
		if(isset($data->monsters[$uuid])) {
			unset($data->monsters[$uuid]);
			return;
		}
		if(isset($data->itemEntities[$uuid])) {
			unset($data->itemEntities[$uuid]);
			return;
		}
	}
}