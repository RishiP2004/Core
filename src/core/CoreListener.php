<?php

declare(strict_types = 1);

namespace core;

use core\utils\Level;

use core\mcpe\event\ServerSettingsRequestEvent;

use pocketmine\event\Listener;
use pocketmine\event\player\{
	PlayerBucketEvent,
	PlayerCreationEvent,
	PlayerChatEvent,
	PlayerCommandPreprocessEvent,
	PlayerExhaustEvent,
	PlayerInteractEvent,
	PlayerItemHeldEvent,
	PlayerItemConsumeEvent,
	PlayerJoinEvent,
	PlayerLoginEvent,
	PlayerMoveEvent,
	PlayerPreLoginEvent,
	PlayerQuitEvent
};
use pocketmine\event\entity\{
	EntityCombustEvent,
	EntityDamageEvent,
	EntityDamageByEntityEvent,
	EntityEffectEvent,
	EntityShootBowEvent
};
use pocketmine\event\block\{
	SignChangeEvent,
	BlockBreakEvent,
	BlockPlaceEvent
};

use pocketmine\event\server\{
	DataPacketReceiveEvent,
	DataPacketSendEvent,
};
use pocketmine\event\inventory\{
	InventoryOpenEvent,
	CraftItemEvent,
	InventoryPickupItemEvent,
	InventoryPickupArrowEvent,
	InventoryTransactionEvent,
};

use pocketmine\inventory\PlayerInventory;

use pocketmine\network\mcpe\protocol\{
	ServerSettingsRequestPacket,
	StartGamePacket
};

class CoreListener implements Listener {
    private $core;

    public function __construct(Core $core) {
        $this->core = $core;
    }
	
    public function onPlayerBucket(PlayerBucketEvent $event) {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
			if(!$player->isInitialized()) {
				$event->setCancelled();
			}
        }
    }

    public function onPlayerCreation(PlayerCreationEvent $event) {
        $event->setPlayerClass(CorePlayer::class);
    }

    public function onPlayerChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
            if(!$player->canChat()) {
                $event->setCancelled(true);
                $player->sendMessage($this->core->getErrorPrefix() . "You are currently in Chat cool down. Upgrade your Rank to reduce this cool down!");
            } else {
				$format = str_replace([
					"{DISPLAY_NAME}",
					"{MESSAGE}"
				], [
					$player->getName(),
					$event->getMessage()
				], $player->getCoreUser()->getRank()->getChatFormat());
				$type = $player->getChatType();

            	if($type !== CorePlayer::NORMAL) {
            		foreach($this->core->getServer()->getOnlinePlayers() as $onlinePlayer) {
            			if($onlinePlayer instanceof CorePlayer) {
            				if($onlinePlayer->getChatType() === $type) {
            					$onlinePlayer->sendMessage($format);
							}
						}
					}
            		$event->setCancelled();
				}
				$event->setFormat($format);
			}
            $player->setChatTime();
        }
    }

    public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event) {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
			if(!$player->isInitialized()) {
				$event->setCancelled();
			}
            $str = str_split($event->getMessage());

            if($str[0] !== "/") {
                return;
            }
        }
    }

    public function onPlayerExhaust(PlayerExhaustEvent $event) {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
        	if(!$player->isInitialized()) {
        		$event->setCancelled();
			}
        }
    }

    public function onPlayerInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
			if(!$player->isInitialized()) {
				$event->setCancelled();
			}
        }
    }
	
	public function onPlayerItemHeld(PlayerItemHeldEvent $event) {
		$player = $event->getPlayer();
		
		if($player instanceof CorePlayer) {
			if(!$player->isInitialized()) {
				$event->setCancelled();
			}
			if($player->isFishing()) {
				if($event->getSlot() !== $player->lastHeldSlot) {
					$player->setFishing(false);
				}
			}
			$player->lastHeldSlot = $event->getSlot();
		}
	}

	public function onPlayerItemConsume(PlayerItemConsumeEvent $event) {
    	$player = $event->getPlayer();

    	if($player instanceof CorePlayer) {
    		if(!$player->isInitialized()) {
    			$event->setCancelled();
			}
		}
	}

    public function onPlayerJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
            $player->setCore($this->core);
			$player->join($player->getCoreUser());
        }
	}

    public function onPlayerLogin(PlayerLoginEvent $event) {
        if($event->getPlayer() instanceof CorePlayer) {
            $event->getPlayer()->setSkin($this->core->getStats()->getStrippedSkin($event->getPlayer()->getSkin()));
        }
    }

    public function onPlayerMove(PlayerMoveEvent $event) {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
			if(!$player->isInitialized()) {
				return;
			}
        }
    }

    public function onPlayerPreLogin(PlayerPreLoginEvent $event) {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
			$this->core->getStats()->getCoreUser($player->getXuid(), function(?CoreUser $user) use($player, $event) {
				$server = $this->core->getNetwork()->getServerFromIp($this->core->getServer()->getIp());
            
				if(count($this->core->getServer()->getOnlinePlayers()) - 1 < $this->core->getServer()->getMaxPlayers()) {
					if($user === null) {
						if(!$server->isWhitelisted()) {
							$this->core->getStats()->registerCoreUser($player);
						}
					} else {
						if(!$server->isWhitelisted()) {
							$player->join($user);
						}
					}	
				} else {
					if($user->loaded()) {
						$player->join($user);
					}
				}
			}); 
        }
    }

    public function onPlayerQuit(PlayerQuitEvent $event) {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
            $player->leave();
        }
    }

	public function onEntityCombust(EntityCombustEvent $event) {
		$player = $event->getEntity();

		if($player instanceof CorePlayer) {
			if(!$player->isInitialized()) {
				$event->setCancelled();
			}
		}
	}

    public function onEntityDamage(EntityDamageEvent $event) {
        $entity = $event->getEntity();

        if($entity instanceof CorePlayer) {
        	$player = $entity;
			
			if(!$player->isInitialized()) {
				$event->setCancelled();
			}
            if($event instanceof EntityDamageByEntityEvent) {
				$damager = $event->getDamager();
				
				if($damager instanceof CorePlayer) {
					if(!$damager->isInitialized()) {
						$event->setCancelled();
					}
				}
			}
		}
	}

	public function onEntityEffect(EntityEffectEvent $event) {
    	$player = $event->getEntity();

    	if($player instanceof CorePlayer) {
    		if(!$player->isInitialized()) {
    			$event->setCancelled();
    		}
    	}
	}

	public function onEntityShootBow(EntityShootBowEvent $event) {
		$player = $event->getEntity();

		if($player instanceof CorePlayer) {
			if(!$player->isInitialized()) {
				$event->setCancelled();
			}
		}
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $event) {
		$player = $event->getPlayer();
		$pk = $event->getPacket();

		if($player instanceof CorePlayer) {
			switch(true) {
				case $pk instanceof ServerSettingsRequestPacket:
					$ev = new ServerSettingsRequestEvent($player);

					$this->core->getServer()->getPluginManager()->callEvent($event);

					if(!$form = $ev->getForm()) {
						$player->sendSetting($form);
					}
				break;
			}
		}
	}

	public function onDataPacketSend(DataPacketSendEvent $event) {
		$pk = $event->getPacket();
		$player = $event->getPlayer();

		switch(true) {
			case $pk instanceof StartGamePacket:
				$pk->dimension = Level::getDimension($player->getLevel());
			break;
		}
	}

	public function onServerSettingsRequest(ServerSettingsRequestEvent $event) {
    	$player = $event->getPlayer();

    	if($player instanceof CorePlayer) {
    		switch($player->getCoreUser()->getServer()->getName()) {
				case "Factions":
					$event->setForm($player->getServerSettingsForm("factions"));
				break;
				case "Lobby":
					$event->setForm($player->getServerSettingsForm("lobby"));
				break;
			}
			$event->setForm($player->getServerSettingsForm());
		}
	}

	public function onSignChange(SignChangeEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			if(!$player->isInitialized()) {
				$event->setCancelled();
			}
		}
	}

	public function onBlockBreak(BlockBreakEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			if(!$player->isInitialized()) {
				$event->setCancelled();
			}
		}
	}

	public function onBlockPlace(BlockPlaceEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			if(!$player->isInitialized()) {
				$event->setCancelled();
			}
		}
	}

	public function onCraftItem(CraftItemEvent $event) {
		$viewer = $event->getPlayer();

		if($viewer instanceof CorePlayer) {
			if(!$viewer->isInitialized()) {
				$event->setCancelled();
			}
		}
	}

	public function onInventoryOpen(InventoryOpenEvent $event) {
		$inventory = $event->getInventory();

		if($inventory instanceof PlayerInventory) {
			$player = $inventory->getHolder();

			if($player instanceof CorePlayer) {
				if(!$player->isInitialized()) {
					$event->setCancelled();
				}
			}
		}
	}

	public function onInventoryPickupArrow(InventoryPickupArrowEvent $event) {
		$inventory = $event->getInventory();

		if($inventory instanceof PlayerInventory) {
			$player = $inventory->getHolder();

			if($player instanceof CorePlayer) {
				if(!$player->isInitialized()) {
					$event->setCancelled();
				}
			}
		}
	}

	public function onInventoryPickupItem(InventoryPickupItemEvent $event) {
		$inventory = $event->getInventory();

		if($inventory instanceof PlayerInventory) {
			$player = $inventory->getHolder();

			if($player instanceof CorePlayer) {
				if(!$player->isInitialized()) {
					$event->setCancelled();
				}
			}
		}
	}

	public function onInventoryTransaction(InventoryTransactionEvent $event) {
		$source = $event->getTransaction()->getSource();

		if($source instanceof CorePlayer) {
			if(!$source->isInitialized()) {
				$event->setCancelled();
			}
		}
	}
}
