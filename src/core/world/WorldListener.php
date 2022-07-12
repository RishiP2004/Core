<?php

declare(strict_types = 1);

namespace core\world;

use core\Core;

use core\player\CorePlayer;

use core\utils\EntityUtils;

use pocketmine\entity\Entity;

use pocketmine\event\Listener;

use pocketmine\event\player\{
	PlayerBedEnterEvent,
	PlayerChatEvent,
	PlayerCommandPreprocessEvent,
	PlayerDropItemEvent,
	PlayerExhaustEvent,
	PlayerInteractEvent,
};
use pocketmine\event\entity\{
	EntityDamageEvent,
	EntityDamageByEntityEvent,
	EntityExplodeEvent,
	ProjectileLaunchEvent,
	ProjectileHitEvent
};

use pocketmine\event\block\{
	BlockBreakEvent,
	BlockPlaceEvent,
	BlockSpreadEvent
};

use pocketmine\event\entity\EntityItemPickupEvent;

use pocketmine\event\inventory\InventoryTransactionEvent;

use pocketmine\event\world\ChunkLoadEvent;

use pocketmine\item\Arrow;

use pocketmine\inventory\{
	PlayerCursorInventory,
	PlayerInventory
};
use pocketmine\inventory\transaction\action\SlotChangeAction;

use pocketmine\world\Position;

class WorldListener implements Listener {
	public function __construct(private WorldManager $manager) {}

	public function onPlayerBedEnter(PlayerBedEnterEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$area = $player->getArea();

			if(!is_null($area)) {
				if(!$player->hasPermission("world.area.playerbedenter")) {
					if(!$area->sleep()) {
						$player->sendMessage(Core::ERROR_PREFIX . "You cannot Sleep in the Area: " . $area->getName());
						$event->cancel();
					}
				}
			}
		}
	}

	public function onPlayerChat(PlayerChatEvent $event) : void {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$area = $player->getArea();

			if(!is_null($area)) {
				if(!$player->hasPermission("world.area.playerchat")) {
					if(!$area->sendChat()) {
						$player->sendMessage(Core::ERROR_PREFIX . "You cannot ChatCommand in the Area: " . $area->getName());
						$event->cancel();
					}
				}
			}
		}
	}

	public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event) : void {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$area = $player->getArea();

			if(!is_null($area)) {
				if(!$player->hasPermission("world.area.playercommandpreprocess")) {
					$command = explode(" ", $event->getMessage())[0];

					if(str_starts_with($command, "/")) {
						if(in_array($command, $area->getBlockedCommands())) {
							$player->sendMessage(Core::ERROR_PREFIX . "You cannot use " . $command . " in the Area: " . $area->getName());
							$event->cancel();
						}
					}
				}
			}
		}
	}

	public function onPlayerDropItem(PlayerDropItemEvent $event) : void {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$area = $player->getArea();

			if(!is_null($area)) {
				if(!$player->hasPermission("world.area.playerdropitem")) {
					if(!$area->itemDrop()) {
						$player->sendMessage(Core::ERROR_PREFIX . "You cannot Drop Items in the Area: " . $area->getName());
						$event->cancel();
					}
				}
			}
		}
	}

	public function onPlayerExhaust(PlayerExhaustEvent $event) : void {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$area = $player->getArea();

			if(!is_null($area)) {
				if(!$player->hasPermission("world.area.playerexhaust")) {
					if(!$area->exhaust()) {
						$event->cancel();
					}
				}
			}
		}
	}

	public function onPlayerInteract(PlayerInteractEvent $event) : void {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$area = $player->getArea();

			if(!is_null($area)) {
				if(!$player->hasPermission("world.area.playerinteract")) {
					if(!$area->usable()) {
						if(in_array($event->getBlock()->getId(), EntityUtils::USABLES)) {
							$player->sendMessage(Core::ERROR_PREFIX . "You cannot Interact with " . $event->getBlock()->getName() . " in the Area: " . $area->getName());
							$event->cancel();
						}
					}
					if(!$area->consume()) {
						if(in_array($event->getBlock()->getId(), EntityUtils::CONSUMABLES)) {
							$player->sendMessage(Core::ERROR_PREFIX . "You cannot Use " . $event->getItem()->getName() . " in the Area: " . $area->getName());
							$event->cancel();
						}
					}
					if(!$area->editable()) {
						if(in_array($event->getBlock()->getId(), EntityUtils::OTHER)) {
							$player->sendMessage(Core::ERROR_PREFIX . "You cannot Edit the Area: " . $area->getName());
							$event->cancel();
						}
					}
				}
			}
		}
	}

	public function onEntityDamage(EntityDamageEvent $event) : void {
		$entity = $event->getEntity();

		if($event->isCancelled()) {
			return;
		}
		$area = $this->manager->getAreaFromPosition($entity->getPosition());

		if(!is_null($area)) {
			if($entity instanceof CorePlayer) {
				if(!$area->damage()) {
					$event->cancel();
				}
			}
			if($event instanceof EntityDamageByEntityEvent) {
				$damager = $event->getDamager();

				if($damager instanceof CorePlayer) {
					if(!$damager->hasPermission("world.area.entitydamage")) {
						if($entity instanceof CorePlayer) {
							if(!$area->damage()) {
								$damager->sendMessage(Core::ERROR_PREFIX . "You cannot PvP in the Area: " . $area->getName());
								$event->cancel();
							}
						}
						if($entity instanceof Entity) {
							if(!$area->entityDamage()) {
								$damager->sendMessage(Core::ERROR_PREFIX . "You cannot damage Entities in the Area: " . $area->getName());
								$event->cancel();
							}
						}
					}
				}
			}
		}
	}

	public function onEntityExplode(EntityExplodeEvent $event) : void {
		foreach($event->getBlockList() as $block) {
			$area = $this->manager->getAreaFromPosition($block->getPosition());

			if(!is_null($area)) {
				if(!$area->explosion()) {
					$event->cancel();
				}
			}
		}
	}

	public function onProjectileLaunch(ProjectileLaunchEvent $event) : void {
		$entity = $event->getEntity();
		$player = $entity->shootingEntity;

		if($player instanceof CorePlayer) {
			$area = $this->manager->getAreaFromPosition($entity->getPosition());

			if(!is_null($area)) {
				if(!$player->hasPermission("world.area.projectilelaunch")) {
					if(!$area->projectile()) {
						$player->sendMessage(Core::ERROR_PREFIX . "You cannot use Projectiles in the Area: " . $area->getName());
						$event->cancel();
					}
				}
			}
		}
	}

	public function onProjectileHit(ProjectileHitEvent $event) : void {
		$entity = $event->getEntity();
		$player = $entity->shootingEntity;

		if($player instanceof CorePlayer) {
			$area = $this->manager->getAreaFromPosition($entity->getPosition());

			if(!is_null($area)) {
				if(!$player->hasPermission("world.area.projectilelaunch")) {
					if(!$area->projectile()) {
						$player->sendMessage(Core::ERROR_PREFIX . "You cannot use Projectiles in the Area: " . $area->getName());
						$event->call();
					}
				}
			}
		}
	}

	public function onBlockBreak(BlockBreakEvent $event) : void {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$area = $this->manager->getAreaFromPosition($event->getBlock()->getPosition());

			if(!is_null($area)) {
				if(!$player->hasPermission("world.area.blockbreak")) {
					if(!$area->editable()) {
						$player->sendMessage(Core::ERROR_PREFIX . "You cannot Break Blocks in the Area: " . $area->getName());
						$event->cancel();
					}
				}
			}
		}
	}

	public function onBlockPlace(BlockPlaceEvent $event) : void {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$area = $this->manager->getAreaFromPosition($event->getBlock()->getPosition());

			if(!is_null($area)) {
				if(!$player->hasPermission("world.area.blockplace")) {
					if(!$area->editable()) {
						$player->sendMessage(Core::ERROR_PREFIX . "You cannot Place Blocks in the Area: " . $area->getName());
						$event->cancel();
					}
				}
			}
		}
	}
	
	public function onBlockSpread(BlockSpreadEvent $event) : void {
        $to = $event->getBlock();
        $area = $this->manager->getAreaFromPosition($to->getPosition());
		
        if(!is_null($area)) {
			if(!$area->editable()) {
				$event->cancel();
			}
        }
    }

	public function onInventoryPickupArrow(EntityItemPickupEvent $event) : void {
		$entity = $event->getEntity();
		$pickedUp = $event->getEntity();

		if($entity instanceof CorePlayer) {
			$area = $entity->getArea();

			if($area->getName() !== "") {
				if($pickedUp instanceof Arrow) {
					if(!$entity->hasPermission("world.area.inventorypickuparrow")) {
						if(!$area->itemPickup()) {
							$entity->sendMessage(Core::ERROR_PREFIX . "You cannot Pickup Items in the Area: " . $area->getName());
							$event->cancel();
						}
					}
				} else {
					if(!$entity->hasPermission("world.area.inventorypickupitem")) {
						if(!$area->itemPickup()) {
							$entity->sendMessage(Core::ERROR_PREFIX . "You cannot Pickup Items in the Area: " . $area->getName());
							$event->cancel();
						}
					}
				}
			}
		}
	}

	public function onInventoryTransaction(InventoryTransactionEvent $event) : void {
		$actions = $event->getTransaction()->getActions();
		$source = $event->getTransaction()->getSource();

		if($source instanceof CorePlayer) {
			$area = $source->getArea();

			if(!is_null($area)) {
				if(!$source->hasPermission("world.area.inventorytransaction")) {
					if(!$area->inventoryTransaction()) {
						foreach($actions as $action) {
							if($action instanceof SlotChangeAction) {
								$inventory = $action->getInventory();

								if($inventory instanceof PlayerInventory or $inventory instanceof PlayerCursorInventory) {
									$source->sendMessage(Core::ERROR_PREFIX . "You cannot do Transactions in your Inventory in the Area: " . $area->getName());
									$event->cancel();
								}
							}
						}
					}
				}
			}
		}
	}
}