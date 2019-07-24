<?php

declare(strict_types = 1);

namespace core\essence;

use core\Core;
use core\CorePlayer;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerMoveEvent;

use pocketmine\event\entity\EntityLevelChangeEvent;

use pocketmine\event\server\DataPacketReceiveEvent;

use core\mcpe\network\InventoryTransactionPacket;

class EssenceEvents implements Listener {
	private $core;

	public function __construct(Core $core) {
		$this->core = $core;
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
				case $pk instanceof InventoryTransactionPacket:
					if($pk->transactionType === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY && $pk->trData->actionType === InventoryTransactionPacket::USE_ITEM_ON_ENTITY_ACTION_INTERACT) {
						$entity = $pk->trData;

						foreach($this->core->getEssence()->getNPCs() as $NPC) {
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