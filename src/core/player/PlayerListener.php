<?php

declare(strict_types = 1);

namespace core\player;

use core\Core;
use core\essential\command\defaults\BlockIpCommand;
use core\network\NetworkManager;
use core\utils\EntityUtils;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\tile\Container;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\Server;

use pocketmine\event\Listener;

use pocketmine\event\player\{
	PlayerBucketEvent,
	PlayerCreationEvent,
	PlayerChatEvent,
	PlayerCommandPreprocessEvent,
	PlayerDropItemEvent,
	PlayerExhaustEvent,
	PlayerInteractEvent,
	PlayerItemHeldEvent,
	PlayerItemConsumeEvent,
	PlayerJoinEvent,
	PlayerLoginEvent,
	PlayerMoveEvent,
	PlayerQuitEvent,
	PlayerKickEvent
};

use pocketmine\event\entity\{
	EntityCombustEvent,
	EntityDamageEvent,
	EntityDamageByEntityEvent,
	EntityEffectEvent,
	EntityShootBowEvent
};
use pocketmine\utils\TextFormat;
use pocketmine\event\block\{
	SignChangeEvent,
	BlockBreakEvent,
	BlockPlaceEvent
};

use pocketmine\event\server\{
	DataPacketReceiveEvent
};

use pocketmine\event\entity\EntityItemPickupEvent;

use pocketmine\event\inventory\{
	InventoryOpenEvent,
	CraftItemEvent,
	InventoryTransactionEvent
};

use pocketmine\inventory\PlayerInventory;

use pocketmine\network\mcpe\protocol\ServerSettingsRequestPacket;
//TODO: MOVE ONTO CUSTOM ITEM!
class PlayerListener implements Listener {
	public function __construct(private PlayerManager $manager) {}
	
	public function onPlayerBucket(PlayerBucketEvent $event) : void {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
			if(!$player->isInitialized()) {
				$event->cancel();
			}
			if($player->isInStaffMode()) {
				$event->cancel();
				$player->sendMessage(Core::ERROR_PREFIX . "You cannot use buckets in Staff Mode.");
			}
        }
    }

	/**
	public function onDataPacketReceive(DataPacketReceiveEvent $event) : void{
		$player = $event->getOrigin()->getPlayer();
		if(($session = PlayerManager::getSession($player)) !== null){
			$packet = $event->getPacket();
			switch($packet->pid()){
				case PlayerAuthInputPacket::NETWORK_ID:
					/** @var PlayerAuthInputPacket $packet */
					/**
					$clientInfo = $session->getClientInfo();
					if($clientInfo?->checkInput($packet->getInputMode()) && $session->isDefaultTag()){
						$player->sendData(SettingsHandler::getPlayersFromType(SettingsHandler::DEVICE), [EntityMetadataProperties::SCORE_TAG => new StringMetadataProperty(PracticeCore::COLOR . $clientInfo->getDeviceOS(true, PracticeCore::isPackEnable()) . TextFormat::GRAY . " | " . TextFormat::WHITE . $clientInfo->getInputAtLogin(true))]);
					}
					break;
			}
		}

	public function onDataPacketSend(DataPacketSendEvent $event) : void{
		if(PracticeCore::isPackEnable()){
			$packets = $event->getPackets();
			foreach($packets as $packet){
				switch($packet->pid()){
					case AddPlayerPacket::NETWORK_ID:
						/** @var AddPlayerPacket $packet */
						/**
						if(($session = PlayerManager::getSession($player = PlayerManager::getPlayerExact($packet->username))) !== null && $session->isDefaultTag()){
							PracticeCore::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($session, $player){
								if($session !== null && $session->isDefaultTag() && $player->isOnline()){
									/** @var ClientInfo $clientInfo */
									/**
									$clientInfo = $session->getClientInfo();
									$player->sendData(SettingsHandler::getPlayersFromType(SettingsHandler::DEVICE), [EntityMetadataProperties::SCORE_TAG => new StringMetadataProperty(PracticeCore::COLOR . $clientInfo->getDeviceOS(true, PracticeCore::isPackEnable()) . TextFormat::GRAY . " | " . TextFormat::WHITE . $clientInfo->getInputAtLogin(true))]);
								}
							}), 5);
						}/**
						break;
					case ResourcePacksInfoPacket::NETWORK_ID:
						/** @var ResourcePacksInfoPacket $packet */
						/**
						$packInfo = PracticeCore::getPacksInfo();
						foreach($packet->resourcePackEntries as $index => $entry){
							if(isset($packInfo[$id = $entry->getPackId()]) && $packInfo[$id] !== $entry->getEncryptionKey()){
								$packet->resourcePackEntries[$index] = new ResourcePackInfoEntry($id, $entry->getVersion(), $entry->getSizeBytes(), $packInfo[$id], "", $id, false);
							}
						}
						break;
				}
			}
		}*/

    /**public function onPlayerCreation(PlayerCreationEvent $event) {
        $event->setPlayerClass(CorePlayer::class);
    }*/

    public function onPlayerChat(PlayerChatEvent $event) : void {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
            if(!$player->canChat()) {
                $event->cancel();
                $player->sendMessage(Core::ERROR_PREFIX . "You are currently in Chat cool down. Upgrade your RankCommand to reduce this cool down!");
            } else {
				$format = $player->getCoreUser()->getRank()->getChatFormatFor($player, $event->getMessage());
				$type = $player->getChatType();

            	if($type !== CorePlayer::NORMAL_CHAT) {
            		foreach(Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
            			if($onlinePlayer instanceof CorePlayer) {
            				if($onlinePlayer->getChatType() === $type) {
            					$onlinePlayer->sendMessage($format);
							}
						}
					}
            		$event->cancel();
				}
				$event->setFormat($format);
			}
            $player->setChatTime();
        }
    }

    public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event) : void {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
			if(!$player->isInitialized()) {
				$event->cancel();
			}
        }
    }

    public function onPlayerExhaust(PlayerExhaustEvent $event) : void {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
        	if(!$player->isInitialized()) {
        		$event->cancel();
			}
			if($player->isInStaffMode()) {
				$event->cancel();
			}
        }
    }

    public function onPlayerInteract(PlayerInteractEvent $event) : void {
        $player = $event->getPlayer();
		$item = $event->getItem();

        if($player instanceof CorePlayer) {
			if(!$player->isInitialized()) {
				$event->cancel();
			}
			if($player->isInStaffMode()) {
				$nbt = $item->getNamedTag();

				if($nbt->getString("vanish", "") !== "") { //dye
					$event->cancel();
					$player->toggleVanish();
					$item = $player->isVanished() ? VanillaItems::LIME_DYE() : VanillaItems::RED_DYE();
					$player->getInventory()->setItemInHand($item)->setCustomName(TextFormat::RESET . ($player->isVanished() ? TextFormat::RED . "Unvanish" : TextFormat::GREEN . "Vanish"));
				}
				if($nbt->getString("randomtp", "") !== "") { //compass
					$event->cancel();
					$players = Server::getInstance()->getOnlinePlayers();
					unset($players[$player->getRawUniqueId()]);

					if(empty($players)) {
						$player->sendMessage(TextFormat::RED . "§l§7[§6!§7]§r §cThere are no players online to teleport to.");
						return;
					}
					$player->teleport($target = $players[array_rand($players)]);
					$player->sendMessage(TextFormat::WHITE . "§l§7[§6!§7]§r §7Teleported to " . TextFormat::LIGHT_PURPLE . $target->getName());
				}
				if($nbt->getString("nearesttp", "") !== "") { //paper
					$event->cancel();
					$target = EntityUtils::getNearestEntityExcept($player, 200, $player, Player::class, false);
					if ($target === null) {
						$player->sendMessage(TextFormat::RED . "§l§7[§6!§7]§r §cNo players were found within 100 blocks of your location!");
					}
					$player->teleport($target);
					$player->sendMessage(TextFormat::WHITE . "§l§7[§6!§7]§r §7Teleported to nearest player: " . TextFormat::LIGHT_PURPLE . $target->getName());
				}
				if($nbt->getString("inventorysee", "") !== "") { //book, this one is chest
					$event->cancel();
					if ($tile = $event->getBlock()->getPosition()->getWorld()->getTile($event->getBlock())) {
						if ($tile instanceof Container) {
							$menu = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST)
								->setName($tile->getName())
								->setListener(InvMenu::readonly());
							$menu->getInventory()->setContents($tile->getInventory()->getContents());
							$menu->send($player);
						}
					}
				}
				if($nbt->getString("freeze", "") !== "") { //ice cube
					$event->cancel();
				}
				if($nbt->getString("unstop", "") !== "") { //stick
					$event->cancel();
					$ignore = [
						BlockLegacyIds::AIR,
						BlockLegacyIds::WATER,
						BlockLegacyIds::FLOWING_LAVA,
						BlockLegacyIds::FLOWING_WATER,
						BlockLegacyIds::TALL_GRASS,
						BlockLegacyIds::DOUBLE_PLANT
					];
					$found = false;
					$through = null;
					$level = $player->getWorld();
					foreach (VoxelRayTrace::inDirection($player->getPosition()->add(0, $player->getEyeHeight()), $player->getDirectionVector(), 25) as $block) {
						if (!in_array($level->getBlockAt($block->x, $block->y, $block->z)->getId(), $ignore, true)) {
							$found = true;
							continue;
						}
						if ($found === true) {
							$through = new Vector3($block->x, $block->y, $block->z);
							break;
						}
					}
					if ($through instanceof Vector3) {
						$player->teleport($through);
						$player->sendMessage("You were teleported through the block!");
					} else {
						$player->sendMessage("There are no blocks to go through");
					}
				}
				if($nbt->getString("gamemode", "") !== "") { //mob head
					$event->cancel();

					if ($player->getGamemode() === GameMode::ADVENTURE()) {
						$player->setGamemode(GameMode::SURVIVAL_VIEWER());
						$player->sendPopup(TextFormat::WHITE . "§l§7[§6!§7]§r §4Spectator");
					} elseif ($player->getGamemode() === GameMode::SURVIVAL_VIEWER()) {
						$player->setGamemode(GameMode::ADVENTURE());
						$player->setAllowFlight(true);
						$player->setFlying(true);
						$player->sendPopup(TextFormat::WHITE . "§l§7[§6!§7]§r §eAdventure");
					}
				}
				//staff chat?
			}
        }
    }
	
	public function onPlayerItemHeld(PlayerItemHeldEvent $event) : void {
		$player = $event->getPlayer();
		
		if($player instanceof CorePlayer) {
			if(!$player->isInitialized()) {
				$event->cancel();
			}
			$player->lastHeldSlot = $event->getSlot();
		}
	}

	public function onPlayerItemConsume(PlayerItemConsumeEvent $event) : void {
    	$player = $event->getPlayer();

    	if($player instanceof CorePlayer) {
    		if(!$player->isInitialized()) {
    			$event->cancel();
			}
		}
	}

    public function onPlayerJoin(PlayerJoinEvent $event) : void {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
			$player->join($player->getCoreUser());
        }
	}

    public function onPlayerMove(PlayerMoveEvent $event) : void {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
			if(!$player->isInitialized()) {
				return;
			}
			if($player->isImmobile()) {
				$player->sendPopup(Core::ERROR_PREFIX . "You can't move while you're frozen!");
				$event->cancel();
			}
        }
    }

    public function onPlayerLogin(PlayerLoginEvent $event)  : void {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
			PlayerManager::getInstance()->getCoreUser($player->getXuid(), function(?CoreUser $user) use($player, $event) {
				$server = NetworkManager::getInstance()->getServerFromIp(Server::getInstance()->getIp());
            
				if(count(Server::getInstance()->getOnlinePlayers()) - 1 < Server::getInstance()->getMaxPlayers()) {
					if(is_null($user)) {
						if(!$server->isWhitelisted()) {
							PlayerManager::getInstance()->registerCoreUser($player);
						}
					} else {
						if(!$server->isWhitelisted()) {
							$player->join($user);
						} else if($server->isWhitelisted() && $user->hasPermission("core.network." . $server->getName() . ".whitelist") or $user->hasPermission("core.network.whitelist")) {
							$player->join($user);
						}
					}	
				} else {
					if($user->loaded()) {
						if($user->hasPermission("core.network." . $server->getName() . ".full")) {
							$player->join($user);
						}
					}
				}
			}); 
        }
    }

    public function onPlayerQuit(PlayerQuitEvent $event) : void {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
            $player->leave();
        }
    }
	
	public function onPlayerKick(PlayerKickEvent $event) : void {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
            $player->getCoreUser()->save();
        }
    }

	public function onDrop(PlayerDropItemEvent $event) : void {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			if(!$player->isInitialized()) {
				return;
			}
			if($player->isInStaffMode()) {
				$event->cancel();
				$player->sendMessage(Core::ERROR_PREFIX . "You cannot drop items in staff mode");
			}
		}
	}

	public function onEntityCombust(EntityCombustEvent $event) : void {
		$player = $event->getEntity();

		if($player instanceof CorePlayer) {
			if(!$player->isInitialized()) {
				$event->cancel();
			}
			if(!$player->isInStaffMode()) {
				$event->cancel();
			}
		}
	}

    public function onEntityDamage(EntityDamageEvent $event) : void {
        $entity = $event->getEntity();
		//deny mob attack maybe
        if($entity instanceof CorePlayer) {
        	$player = $entity;
			
			if(!$player->isInitialized()) {
				$event->cancel();
			}
			if ($player->isImmobile() or $player->isInStaffMode()) {
				$event->cancel();
			}
            if($event instanceof EntityDamageByEntityEvent) {
				$damager = $event->getDamager();
				
				if($damager instanceof CorePlayer) {
					if(!$damager->isInitialized()) {
						$event->cancel();
					}
					if($damager->isInStaffMode()) {
						$event->cancel();

						$entity = $event->getEntity();
						$item = $damager->getInventory()->getItemInHand();

						$nbt = $item->getNamedTag();

						if($nbt->getString("Freeze", "") !== "") {
							$entity->setFrozen(!$entity->isFrozen());

							if($entity->isFrozen()) {
								$entity->sendMessage(Core::PREFIX . "You have been Frozen!");
								$damager->sendMessage(Core::PREFIX . "You have frozen " . $entity->getName()());

							} else {
								$entity->sendMessage(Core::PREFIX . "You have been Unfrozen!");
								$damager->sendMessage(Core::PREFIX . "You have Unfrozen " . $entity->getName()());
							}
						}
						if($nbt->getString("Invsee", "") !== "") {
							Server::getInstance()->dispatchCommand($damager, "invsee \"" . $entity->getName() . "\"");
						}
					}
				}
			}
		}
	}

	public function onEntityEffect(EntityEffectEvent $event) : void {
    	$player = $event->getEntity();

    	if($player instanceof CorePlayer) {
    		if(!$player->isInitialized()) {
    			$event->cancel();
    		}
    	}
	}

	public function onEntityShootBow(EntityShootBowEvent $event) : void {
		$player = $event->getEntity();

		if($player instanceof CorePlayer) {
			if(!$player->isInitialized()) {
				$event->cancel();
			}
		}
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $event) : void {
		$origin = $event->getOrigin();
		$pk = $event->getPacket();
		$player = $origin->getPlayer();

		if($player instanceof CorePlayer) {
			if($pk instanceof ServerSettingsRequestPacket) {
				switch($player->getCoreUser()->getServer()->getName()) {
					case "HCF":
						$player->sendSettingForm($player->getServerSettingsForm("hcf"));
						break;
					case "Lobby":
						$player->sendSettingForm($player->getServerSettingsForm("lobby"));
						break;
				}
				$player->sendSettingForm($player->getServerSettingsForm());
			}
		}
	}

	public function onSignChange(SignChangeEvent $event) : void {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			if(!$player->isInitialized()) {
				$event->cancel();
			}
		}
	}

	public function onBlockBreak(BlockBreakEvent $event) : void {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			if(!$player->isInitialized()) {
				$event->cancel();
			}
		}
	}

	public function onBlockPlace(BlockPlaceEvent $event) : void {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			if(!$player->isInitialized()) {
				$event->cancel();
			}
		}
	}

	public function onCraftItem(CraftItemEvent $event) : void {
		$viewer = $event->getPlayer();

		if($viewer instanceof CorePlayer) {
			if(!$viewer->isInitialized()) {
				$event->cancel();
			}
		}
	}

	public function onInventoryOpen(InventoryOpenEvent $event) : void {
		$inventory = $event->getInventory();

		if($inventory instanceof PlayerInventory) {
			$player = $inventory->getHolder();

			if($player instanceof CorePlayer) {
				if(!$player->isInitialized()) {
					$event->cancel();
				}
			}
		}
	}

	public function onInventoryPickupArrow(EntityItemPickupEvent $event) : void {
		$entity = $event->getEntity();

		if($entity instanceof CorePlayer) {
			if(!$entity->isInitialized()) {
				$event->cancel();
			}
			if($entity->isInStaffMode()) {
				$event->cancel();
				$entity->sendMessage(Core::ERROR_PREFIX . "You cannot drop items in staff mode");
			}
		}
	}

	public function onInventoryTransaction(InventoryTransactionEvent $event) : void {
		$source = $event->getTransaction()->getSource();

		if($source instanceof CorePlayer) {
			if(!$source->isInitialized()) {
				$event->cancel();
			}
			if($source->isInStaffMode()) {
				$event->cancel();
			}
		}
	}
}
