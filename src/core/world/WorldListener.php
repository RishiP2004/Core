<?php

declare(strict_types = 1);

namespace core\world;

use core\Core;
use core\CorePlayer;

use core\utils\Entity;

use core\world\area\Lobby;

use pocketmine\event\Listener;

use pocketmine\event\player\{
	PlayerBedEnterEvent,
	PlayerChatEvent,
	PlayerCommandPreprocessEvent,
	PlayerDropItemEvent,
	PlayerExhaustEvent,
	PlayerInteractEvent,
	PlayerMoveEvent,
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
	BlockPlaceEvent
};

use pocketmine\event\inventory\{
	InventoryPickupArrowEvent,
	InventoryPickupItemEvent,
	InventoryTransactionEvent
};

use pocketmine\event\level\ChunkLoadEvent;

use pocketmine\level\Position;

use pocketmine\inventory\{
	PlayerCursorInventory,
	PlayerInventory
};
use pocketmine\inventory\transaction\action\SlotChangeAction;

class WorldListener implements Listener {
	private $manager;

	public function __construct(World $manager) {
		$this->manager = $manager;
	}

	public function onPlayerBedEnter(PlayerBedEnterEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$area = $player->getArea();

			if(!is_null($area)) {
				if(!$player->hasPermission("core.world.area.playerbedenter")) {
					if(!$area->sleep()) {
						$player->sendMessage(Core::ERROR_PREFIX . "You cannot Sleep in the Area: " . $area->getName());
						$event->setCancelled();
					}
				}
			}
		}
	}

	public function onPlayerChat(PlayerChatEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$area = $player->getArea();

			if(!is_null($area)) {
				if(!$player->hasPermission("core.world.area.playerchat")) {
					if(!$area->sendChat()) {
						$player->sendMessage(Core::ERROR_PREFIX . "You cannot Chat in the Area: " . $area->getName());
						$event->setCancelled();
					}
				}
			}
		}
	}

	public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$area = $player->getArea();

			if(!is_null($area)) {
				if(!$player->hasPermission("core.world.area.playercommandpreprocess")) {
					$command = explode(" ", $event->getMessage())[0];

					if(substr($command, 0, 1) === "/") {
						if(in_array($command, $area->getBlockedCommands())) {
							$player->sendMessage(Core::ERROR_PREFIX . "You cannot use " . $command . " in the Area: " . $area->getName());
							$event->setCancelled();
						}
					}
				}
			}
		}
	}

	public function onPlayerDropItem(PlayerDropItemEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$area = $player->getArea();

			if(!is_null($area)) {
				if(!$player->hasPermission("core.world.area.playerdropitem")) {
					if(!$area->itemDrop()) {
						$player->sendMessage(Core::ERROR_PREFIX . "You cannot Drop Items in the Area: " . $area->getName());
						$event->setCancelled();
					}
				}
			}
		}
	}

	public function onPlayerExhaust(PlayerExhaustEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$area = $player->getArea();

			if(!is_null($area)) {
				if(!$player->hasPermission("core.world.area.playerexhaust")) {
					if(!$area->exhaust()) {
						$event->setCancelled();
					}
				}
			}
		}
	}

	public function onPlayerInteract(PlayerInteractEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$area = $player->getArea();

			if(!is_null($area)) {
				if(!$player->hasPermission("core.world.area.playerinteract")) {
					if(!$area->usable()) {
						if(in_array($event->getBlock()->getId(), Entity::USABLES)) {
							$player->sendMessage(Core::ERROR_PREFIX . "You cannot Interact with " . $event->getBlock()->getName() . " in the Area: " . $area->getName());
							$event->setCancelled();
						}
					}
					if(!$area->consume()) {
						if(in_array($event->getBlock()->getId(), Entity::CONSUMABLES)) {
							$player->sendMessage(Core::ERROR_PREFIX . "You cannot Use " . $event->getItem()->getName() . " in the Area: " . $area->getName());
							$event->setCancelled();
						}
					}
					if(!$area->editable()) {
						if(in_array($event->getBlock()->getId(), Entity::OTHER)) {
							$player->sendMessage(Core::ERROR_PREFIX . "You cannot Edit the Area: " . $area->getName());
							$event->setCancelled();
						}
					}
				}
			}
		}
	}

	public function onPlayerMove(PlayerMoveEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			if(!$event->getFrom()->equals($event->getTo())) {
				if($player->updateArea()) {
					$player->setMotion($event->getFrom()->subtract($player->getLocation()->normalize()->multiply(4)));
				}
			}
			if(!is_null($area = $player->getArea())) {
				if($area instanceof Lobby && $event->getTo()->getFloorY() < 0) {
					$player->teleport($player->getLevel()->getSafeSpawn());
				}
			}
		}
	}

	public function onEntityDamage(EntityDamageEvent $event) {
		$entity = $event->getEntity();

		if($event->isCancelled()) {
			return;
		}
		$area = $this->manager->getAreaFromPosition($entity);

		if(!is_null($area)) {
			if($entity instanceof CorePlayer) {
				if(!$area->damage()) {
					$event->setCancelled(true);
				}
			}
			if($event instanceof EntityDamageByEntityEvent) {
				$damager = $event->getDamager();

				if($damager instanceof CorePlayer) {
					if(!$damager->hasPermission("core.world.area.entitydamage")) {
						if($entity instanceof CorePlayer) {
							if(!$area->damage()) {
								$damager->sendMessage(Core::ERROR_PREFIX . "You cannot PvP in the Area: " . $area->getName());
								$event->setCancelled(true);
							}
						}
						if($entity instanceof Entity) {
							if(!$area->entityDamage()) {
								$damager->sendMessage(Core::ERROR_PREFIX . "You cannot damage Entities in the Area: " . $area->getName());
								$event->setCancelled();
							}
						}
					}
				}
			}
		}
	}

	public function onEntityExplode(EntityExplodeEvent $event) {
		foreach($event->getBlockList() as $block) {
			$area = $this->manager->getAreaFromPosition($block);

			if(!is_null($area)) {
				if(!$area->explosion()) {
					$event->setCancelled();
				}
			}
		}
	}

	public function onProjectileLaunch(ProjectileLaunchEvent $event) {
		$entity = $event->getEntity();
		$player = $entity->shootingEntity;

		if($player instanceof CorePlayer) {
			$area = $this->manager->getAreaFromPosition($entity);

			if(!is_null($area)) {
				if(!$player->hasPermission("core.world.area.projectilelaunch")) {
					if(!$area->projectile()) {
						$player->sendMessage(Core::ERROR_PREFIX . "You cannot use Projectiles in the Area: " . $area->getName());
						$event->setCancelled();
					}
				}
			}
		}
	}

	public function onProjectileHit(ProjectileHitEvent $event) {
		$entity = $event->getEntity();
		$player = $entity->shootingEntity;

		if($player instanceof CorePlayer) {
			$area = $this->manager->getAreaFromPosition($entity);

			if(!is_null($area)) {
				if(!$player->hasPermission("core.world.area.projectilelaunch")) {
					if(!$area->projectile()) {
						$player->sendMessage(Core::ERROR_PREFIX . "You cannot use Projectiles in the Area: " . $area->getName());
						$event->setCancelled();
					}
				}
			}
		}
	}

	public function onBlockBreak(BlockBreakEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$area = $this->manager->getAreaFromPosition($event->getBlock());

			if(!is_null($area)) {
				if(!$player->hasPermission("core.world.area.blockbreak")) {
					if(!$area->editable()) {
						$player->sendMessage(Core::ERROR_PREFIX . "You cannot Break Blocks in the Area: " . $area->getName());
						$event->setCancelled();
					}
				}
			}
		}
	}

	public function onBlockPlace(BlockPlaceEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$area = $this->manager->getAreaFromPosition($event->getBlock());

			if(!is_null($area)) {
				if(!$player->hasPermission("core.world.area.blockplace")) {
					if(!$area->editable()) {
						$player->sendMessage(Core::ERROR_PREFIX . "You cannot Place Blocks in the Area: " . $area->getName());
						$event->setCancelled();
					}
				}
			}
		}
	}

	public function onInventoryPickupArrow(InventoryPickupArrowEvent $event) {
		$viewer = $event->getViewers();

		if($viewer instanceof CorePlayer) {
			$area = $viewer->getArea();

			if($area->getName() !== "") {
				if(!$viewer->hasPermission("core.world.area.inventorypickuparrow")) {
					if(!$area->itemPickup()) {
						$viewer->sendMessage(Core::ERROR_PREFIX . "You cannot Pickup Items in the Area: " . $area->getName());
						$event->setCancelled();
					}
				}
			}
		}
	}

	public function onInventoryPickupItem(InventoryPickupItemEvent $event) {
		$viewer = $event->getViewers();

		if($viewer instanceof CorePlayer) {
			$area = $viewer->getArea();

			if(!is_null($area)) {
				if(!$viewer->hasPermission("core.world.area.inventorypickupitem")) {
					if(!$area->itemPickup()) {
						$viewer->sendMessage(Core::ERROR_PREFIX . "You cannot Pickup Items in the Area: " . $area->getName());
						$event->setCancelled();
					}
				}
			}
		}
	}

	public function onInventoryTransaction(InventoryTransactionEvent $event) {
		$actions = $event->getTransaction()->getActions();
		$source = $event->getTransaction()->getSource();

		if($source instanceof CorePlayer) {
			$area = $source->getArea();

			if(!is_null($area)) {
				if(!$source->hasPermission("core.world.area.inventorytransaction")) {
					if(!$area->inventoryTransaction()) {
						foreach($actions as $action) {
							if($action instanceof SlotChangeAction) {
								$inventory = $action->getInventory();

								if($inventory instanceof PlayerInventory or $inventory instanceof PlayerCursorInventory) {
									$source->sendMessage(Core::ERROR_PREFIX . "You cannot do Transactions in your Inventory in the Area: " . $area->getName());
									$event->setCancelled();
								}
							}
						}
					}
				}
			}
		}
	}

	public function onChunkLoad(ChunkLoadEvent $event) {
		$chunk = $event->getChunk();
		$area = $this->manager->getAreaFromPosition(new Position($chunk->getX(), $chunk->getMaxY(), $chunk->getZ()));

		if(!is_null($area)) {
			if(!$area->entitySpawn()) {
				return;
			}
		}
	}
}