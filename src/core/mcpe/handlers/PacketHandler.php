<?php

namespace core\mcpe\handlers;

use CortexPE\entity\vehicle\Minecart;
use CortexPE\Main;
use CortexPE\network\InventoryTransactionPacket;
use CortexPE\Session;
use CortexPE\Utils;
use pocketmine\event\{
	Listener, server\DataPacketReceiveEvent, server\DataPacketSendEvent
};
use pocketmine\network\mcpe\protocol\{
	PlayerActionPacket, PlayerInputPacket, StartGamePacket
};
use pocketmine\Player as PMPlayer;
use pocketmine\plugin\Plugin;

class PacketHandler implements Listener {

	/** @var Plugin */
	public $plugin;

	public function __construct(Plugin $plugin){
		$this->plugin = $plugin;
	}

	/**
	 * @param DataPacketReceiveEvent $ev
	 *
	 * @priority LOWEST
	 */
	public function onPacketReceive(DataPacketReceiveEvent $ev){
		$pk = $ev->getPacket();
		$p = $ev->getPlayer();

		switch(true){
			case ($pk instanceof PlayerActionPacket):
				$session = Main::getInstance()->getSessionById($p->getId());
				if($session instanceof Session){
					switch($pk->action){
						case PlayerActionPacket::ACTION_DIMENSION_CHANGE_ACK:
						case PlayerActionPacket::ACTION_DIMENSION_CHANGE_REQUEST:
							$pk->action = PlayerActionPacket::ACTION_RESPAWN; // redirect to respawn action so that PMMP would handle it as a respawn
							break;

						case PlayerActionPacket::ACTION_START_GLIDE:
							if(Main::$elytraEnabled){
								$p->setGenericFlag(PMPlayer::DATA_FLAG_GLIDING, true); // Why isn't the datatype a byte?

								$session->usingElytra = $session->allowCheats = true;
							}
							break;
						case PlayerActionPacket::ACTION_STOP_GLIDE:
							if(Main::$elytraEnabled){
								$p->setGenericFlag(PMPlayer::DATA_FLAG_GLIDING, false);

								$session->usingElytra = $session->allowCheats = false;

								$session->damageElytra();
							}
							break;
						case PlayerActionPacket::ACTION_START_SWIMMING:
							$p->setGenericFlag(PMPlayer::DATA_FLAG_SWIMMING, true);
							break;
						case PlayerActionPacket::ACTION_STOP_SWIMMING:
							$p->setGenericFlag(PMPlayer::DATA_FLAG_SWIMMING, false);
							break;
					}
				}
				break;
			case ($pk instanceof InventoryTransactionPacket): // TODO: Remove this once https://github.com/pmmp/PocketMine-MP/pull/2124 gets merged
				if($pk->transactionType == InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY){
					if($pk->trData->actionType == InventoryTransactionPacket::USE_ITEM_ON_ENTITY_ACTION_INTERACT){
						$entity = $p->getLevel()->getEntity($pk->trData->entityRuntimeId);
						$item = $p->getInventory()->getItemInHand();
						$slot = $pk->trData->hotbarSlot;
						$clickPos = $pk->trData->clickPos;
						if(method_exists($entity, "onInteract")){
							//                  Player Item  Int   Vector3
							$entity->onInteract($p, $item, $slot, $clickPos);
						}

						/*if($item instanceof Lead){
							if(Utils::leashEntityToPlayer($p, $entity)){
								if($p->isSurvival()){
									$item->count--;
								}
							} else {
								$p->getLevel()->dropItem($entity, $item);
							}
						}*/
					}
				}
				break;
			case ($pk instanceof PlayerInputPacket):
				if(isset($p->riding) && $p->riding instanceof Minecart){
					/** @var $riding Minecart */
					$riding = $p->riding;
					$riding->setCurrentSpeed($pk->motionY);
				}
				// Cancer this event, this avoid the packet being unhandled
				$ev->setCancelled();
				break;
		}
	}

	/**
	 * @param DataPacketSendEvent $ev
	 *
	 * @priority LOWEST
	 */
	public function onPacketSend(DataPacketSendEvent $ev){
		$pk = $ev->getPacket();
		$p = $ev->getPlayer();
		switch(true){
			case ($pk instanceof StartGamePacket):
				if(Main::$registerDimensions){
					$pk->dimension = Utils::getDimension($p->getLevel());
				}
				break;
		}
	}
}
