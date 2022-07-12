<?php

declare(strict_types = 1);

namespace core\essence;

use core\essence\npc\NPC;
use core\player\CorePlayer;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerMoveEvent;

use pocketmine\event\entity\EntityTeleportEvent;

use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;

class EssenceListener implements Listener {
	public function __construct(private EssenceManager $manager) {}
	//TODO
	/**
	public function onPlayerMove(PlayerMoveEvent $event) : void {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			foreach($this->manager->getNPCs() as $NPC) {
				if($NPC instanceof NPC) {
					if($NPC->rotate()) {
						$NPC->rotateTo($player);
					}
				}
			}
		}
	}*/

	public function onEntityLevelChange(EntityTeleportEvent $event) : void {
		$entity = $event->getEntity();

		if($entity instanceof CorePlayer) {
			foreach($this->manager->getHolograms() as $hologram) {
				if(!$hologram->getPosition()->getWorld()->getFolderName() == $event->getTo()->getWorld()->getFolderName()) {
					return;
				} else {
					$hologram->spawnTo($entity);
				}
			}
		}
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $event) : void {
		$player = $event->getOrigin()->getPlayer();
		$pk = $event->getPacket();

		if($player instanceof CorePlayer) {
			if($pk->pid() == InventoryTransactionPacket::NETWORK_ID) {
				if($pk->trData->getTypeId() == InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY) {
					if($pk->trData->getActionType() === UseItemOnEntityTransactionData::ACTION_ATTACK) {
						$entity = $pk->trData;

						foreach($this->manager->getNPCs() as $NPC) {
							if($entity->getActorRuntimeId() === $NPC->getEntityId()) {
								$NPC->onInteract($player);
							}
						}
					}
				}
			}
		}
	}
}