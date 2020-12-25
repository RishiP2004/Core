<?php

declare(strict_types = 1);

namespace core\essence;

use core\CorePlayer;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;

class EssenceListener implements Listener {
	private $manager;

	public function __construct(Essence $manager) {
		$this->manager = $manager;
	}

	public function onPlayerMove(PlayerMoveEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$player->rotateNPCs();
		}
	}

	public function onEntityLevelChange(EntityLevelChangeEvent $event) {
		$entity = $event->getEntity();

		if($entity instanceof CorePlayer) {
			$entity->checkNPCLevelChange();
			$entity->checkFloatingTextsLevelChange($event->getTarget());
		}
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $event) {
		$player = $event->getPlayer();
		$pk = $event->getPacket();

		if($player instanceof CorePlayer) {
			switch(true) {
				case $pk instanceof \pocketmine\network\mcpe\protocol\InventoryTransactionPacket:
					if($pk->transactionType === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY) {
						$entity = $pk->trData;

						foreach($this->manager->getNPCs() as $NPC) {
							if($entity->entityRuntimeId === $NPC->getEID()) {
								$NPC->onInteract($player);
							}
						}
					}
				break;
			}
		}
	}
}