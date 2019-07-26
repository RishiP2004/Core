<?php

declare(strict_types = 1);

namespace core\mcpe;

use core\Core;
use core\CorePlayer;

use core\mcpe\entity\{
	AnimalBase,
	MonsterBase,
	CreatureBase
};
use core\mcpe\item\Elytra;
use core\mcpe\block\SlimeBlock;
use core\mcpe\entity\vehicle\Minecart;
use core\mcpe\network\InventoryTransactionPacket;

use pocketmine\event\Listener;

use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\event\level\{
	ChunkLoadEvent,
	ChunkUnloadEvent
};

use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\{
	PlayerActionPacket,
	PlayerInputPacket
};

use pocketmine\entity\{
	Animal,
	Monster
};

use pocketmine\level\{
	Position,
	Level
};

class MCPEListener implements Listener, Addon {
	private $core;

	public function __construct(Core $core) {
		$this->core = $core;
	}

	public function onEntityDamage(EntityDamageEvent $event) {
		$entity = $event->getEntity();

		if($entity instanceof CorePlayer) {
			if($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
				if($entity->getArmorInventory()->getChestplate() instanceof Elytra or $event->getEntity()->getLevel()->getBlock($event->getEntity()->subtract(0, 1, 0))->getId() === SlimeBlock::class) {
					$event->setCancelled(true);
				}
			}
		}
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $event) {
		$player = $event->getPlayer();
		$pk = $event->getPacket();

		if($player instanceof CorePlayer) {
			switch(true) {
				case $pk instanceof InventoryTransactionPacket:
					if($pk->transactionType === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY && $pk->trData->actionType === InventoryTransactionPacket::USE_ITEM_ON_ENTITY_ACTION_INTERACT) {
						if($player->getLevel() instanceof Level) {
							$entity = $player->getLevel()->getEntity($pk->trData->entityRuntimeId);
							$item = $player->getInventory()->getItemInHand();
							$slot = $pk->trData->hotbarSlot;
							$clickPos = $pk->trData->clickPos;

							if(method_exists($entity, "onInteract")) {
								$entity->onInteract($player, $item, $slot, $clickPos);
							}
						}
					}
				break;
				case $pk instanceof PlayerActionPacket:
					switch($pk->action) {
						case PlayerActionPacket::ACTION_DIMENSION_CHANGE_ACK:
						case PlayerActionPacket::ACTION_DIMENSION_CHANGE_REQUEST:
							$pk->action = PlayerActionPacket::ACTION_RESPAWN;
						break;
						case PlayerActionPacket::ACTION_START_GLIDE:
							$player->setGenericFlag(CorePlayer::DATA_FLAG_GLIDING, true);

							$player->usingElytra = $player->allowCheats = true;
						break;
						case PlayerActionPacket::ACTION_STOP_GLIDE:
							$player->setGenericFlag(CorePlayer::DATA_FLAG_GLIDING, false);

							$player->usingElytra = $player->allowCheats = false;

							if(!$player->isAlive() || !$player->isSurvival()) {
								return;
							}
							$inv = $player->getArmorInventory();
							$elytra = $inv->getChestplate();

							if($elytra instanceof Elytra) {
								$elytra->applyDamage(1);
							}
						break;
						case PlayerActionPacket::ACTION_START_SWIMMING:
							$player->setGenericFlag(CorePlayer::DATA_FLAG_SWIMMING, true);
						break;
						case PlayerActionPacket::ACTION_STOP_SWIMMING:
							$player->setGenericFlag(CorePlayer::DATA_FLAG_SWIMMING, false);
						break;
					}
					break;
				case $pk instanceof PlayerInputPacket:
					if(isset($player->riding) && $player->riding instanceof Minecart) {
						/** @var $riding Minecart */
						$riding = $player->riding;

						$riding->setCurrentSpeed($pk->motionY);
					}
					$event->setCancelled();
				break;
			}
		}
	}
	public function onChunkLoad(ChunkLoadEvent $event) {
		$chunk = $event->getChunk();
		$level = $event->getLevel();
		$packCenter = new Vector3(mt_rand($chunk->getX() << 4, (($chunk->getX() << 4) + 15)), mt_rand(0, $level->getWorldHeight() - 1), mt_rand($chunk->getZ() << 4, (($chunk->getZ() << 4) + 15)));
		$lightLevel = $level->getFullLightAt($packCenter->x, $packCenter->y, $packCenter->z);

		if(!self::ENTITY_SPAWN) {
			return;
		}
		if(!$level->getBlockAt($packCenter->x, $packCenter->y, $packCenter->z)->isSolid() and $lightLevel > 8) {
			$biomeId = $level->getBiomeId($packCenter->x, $packCenter->z);

			if(array_key_exists($biomeId, $this->core->getMCPE()::BIOME_ANIMALS)) {
				$entityList = $this->core->getMCPE()::BIOME_ANIMALS[$biomeId];
			} else {
				$entityList = $this->core->getMCPE()::BIOME_ANIMALS[$biomeId = 1];
			}
			if(empty($entityList)) {
				return;
			}
			$entityId = $entityList[array_rand($this->core->getMCPE()::BIOME_ANIMALS[$biomeId])];

			if(!$level->getBlockAt($packCenter->x, $packCenter->y, $packCenter->z)->isSolid()) {
				for($attempts = 0, $currentPackSize = 0; $attempts <= 12 and $currentPackSize < 4; $attempts++) {
					$x = mt_rand(-20, 20) + $packCenter->x;
					$z = mt_rand(-20, 20) + $packCenter->z;

					foreach($this->core->getMCPE()->registeredEntities as $class => $param) {
						if($class instanceof AnimalBase or $class instanceof Animal and $class::NETWORK_ID === $entityId) {
							$entity = $class::spawnMob(new Position($x + 0.5, $packCenter->y, $z + 0.5, $level));

							if($entity !== null) {
								$currentPackSize++;
							}
						}
					}
				}
			}
		} else if(!$level->getBlockAt($packCenter->x, $packCenter->y, $packCenter->z)->isSolid() and $lightLevel <= 7) {
			$biomeId = $level->getBiomeId($packCenter->x, $packCenter->z);

			if(array_key_exists($biomeId, $this->core->getMCPE()::BIOME_ANIMALS)) {
				$entityList = $this->core->getMCPE()::BIOME_HOSTILE_MOBS[$biomeId];
			} else {
				$entityList = $this->core->getMCPE()::BIOME_HOSTILE_MOBS[$biomeId = 1];
			}
			if(empty($entityList)) {
				return;
			}
			$entityId = $entityList[array_rand($this->core->getMCPE()::BIOME_HOSTILE_MOBS[$biomeId])];

			if(!$level->getBlockAt($packCenter->x, $packCenter->y, $packCenter->z)->isSolid()) {
				for($attempts = 0, $currentPackSize = 0; $attempts <= 12 and $currentPackSize < 4; $attempts++) {
					$x = mt_rand(-20, 20) + $packCenter->x;
					$z = mt_rand(-20, 20) + $packCenter->z;

					foreach($this->core->getMCPE()->registeredEntities as $class => $param) {
						if($class instanceof MonsterBase or $class instanceof Monster and $class::NETWORK_ID === $entityId) {
							$entity = $class::spawnMob(new Position($x + 0.5, $packCenter->y, $z + 0.5, $level));

							if($entity !== null) {
								$currentPackSize++;
							}
						}
					}
				}
			}
		}
	}

	public function onChunkUnload(ChunkUnloadEvent $event) {
		$chunk = $event->getChunk();

		if(!self::ENTITY_DESPAWN) {
			return;
		}
		foreach($chunk->getEntities() as $entity) {
			if($entity instanceof CreatureBase or $entity instanceof Monster or $entity instanceof Animal and !$entity->isPersistent()) {
				$entity->flagForDespawn();
			}
		}
	}
}