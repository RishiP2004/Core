<?php

declare(strict_types = 1);

namespace core\broadcast;

use core\Core;
use core\CorePlayer;
use core\CoreUser;

use core\stats\rank\Rank;

use pocketmine\event\Listener;

use pocketmine\event\player\{
	PlayerDeathEvent,
	PlayerJoinEvent,
	PlayerPreLoginEvent,
	PlayerQuitEvent
};

use pocketmine\event\entity\{
	EntityDamageEvent,
	EntityDamageByBlockEvent,
	EntityLevelChangeEvent
};

use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\network\mcpe\protocol\{
	LoginPacket,
	ProtocolInfo
};

use pocketmine\entity\Living;

class BroadcastListener implements Listener, Broadcasts {
	private $core;

	public function __construct(Core $core) {
		$this->core = $core;
	}

	public function onPlayerDeath(PlayerDeathEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$replaces = [
				"{PLAYER}" => $player->getName()
			];
			$message = "";
			$cause = $player->getLastDamageCause();

			switch($cause->getCause()) {
				case EntityDamageEvent::CAUSE_CONTACT:
					$stringCause = "contact";

					if($cause instanceof EntityDamageByBlockEvent) {
						$replaces["{BLOCK}"] = $cause->getDamager()->getName();
						break;
					}
					$replaces["{BLOCK}"] = "unknown";
					break;
				case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
					$stringCause = "kill";
					$killer = $cause->getEntity();

					if($killer instanceof Living) {
						$array["{KILLER}"] = $killer->getName();
						break;
					}
					$array["{KILLER}"] = "unknown";
					break;
				case EntityDamageEvent::CAUSE_PROJECTILE:
					$stringCause = "projectile";
					$killer = $cause->getEntity();

					if($killer instanceof Living) {
						$array["{KILLER}"] = $killer->getName();
						break;
					}
					$array["{KILLER}"] = "unknown";
					break;
				case EntityDamageEvent::CAUSE_SUFFOCATION:
					$stringCause = "suffocation";
					break;
				case EntityDamageEvent::CAUSE_STARVATION:
					$stringCause = "starvation";
					break;
				case EntityDamageEvent::CAUSE_FALL:
					$stringCause = "fall";
					break;
				case EntityDamageEvent::CAUSE_FIRE:
					$stringCause = "fire";
					break;
				case EntityDamageEvent::CAUSE_FIRE_TICK:
					$stringCause = "on-fire";
					break;
				case EntityDamageEvent::CAUSE_LAVA:
					$stringCause = "lava";
					break;
				case EntityDamageEvent::CAUSE_DROWNING:
					$stringCause = "drowning";
					break;
				case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
				case EntityDamageEvent::CAUSE_BLOCK_EXPLOSION:
					$stringCause = "explosion";
					break;
				case EntityDamageEvent::CAUSE_VOID:
					$stringCause = "void";
					break;
				case EntityDamageEvent::CAUSE_SUICIDE:
					$stringCause = "suicide";
					break;
				case EntityDamageEvent::CAUSE_MAGIC:
					$stringCause = "magic";
					break;
				default:
					$stringCause = "normal";
					break;
			}
			if(!empty(self::DEATHS[$stringCause])) {
				$message = self::JOINS[$stringCause];

				foreach($replaces as $key => $value) {
					$message = str_replace([
						"{" . $key . "}",
						"{NAME_TAG_FORMAT}"
					], [
						$value,
						str_replace("{DISPLAY_NAME}", $player->getName(), $player->getCoreUser()->getRank()->getNameTagFormat())
					], $message);
				}
			}
			$event->setDeathMessage($message);
		}
	}

	public function onPlayerJoin(PlayerJoinEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$message = "";

			if(!$player->hasPlayedBefore()) {
				if(!empty(self::JOINS["first"])) {
					foreach(Core::getInstance()->getStats()->getRanks() as $r) {
						if($r->getValue() === Rank::DEFAULT) {
							$rank = $r->getNameTagFormat();
						}
					}
					$message = str_replace([
						"{PLAYER}",
						"{TIME}",
						"{NAME_TAG_FORMAT}"
					], [
						$player->getName(),
						date(self::FORMATS["date_time"]),
						str_replace("{DISPLAY_NAME}", $player->getName(), $rank)
					], self::JOINS["first"]);
				}
			} else {
				if($player->hasPermission("core.broadcast.join")) {
					if(!empty(self::JOINS["normal"])) {
						$message = str_replace([
							"{PLAYER}",
							"{TIME}",
							"{NAME_TAG_FORMAT}"
						], [
							$player->getName(),
							date(self::FORMATS["date_time"]),
							str_replace("{DISPLAY_NAME}", $player->getName(), $player->getCoreUser()->getRank()->getNameTagFormat())
						], self::JOINS["normal"]);
					}
				}
			}
			$event->setJoinMessage($message);
		}
	}

	public function onPlayerPreLogin(PlayerPreLoginEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$this->core->getStats()->getCoreUser($player->getXuid(), function(?CoreUser $user) use($player, $event) {
				$message = "";

				$server = $this->core->getNetwork()->getServerFromIp($this->core->getServer()->getIp());

				if(count($this->core->getServer()->getOnlinePlayers()) - 1 < $this->core->getServer()->getMaxPlayers()) {
					if(!empty(self::KICKS["whitelisted"])) {
						$message = str_replace([
							"{PLAYER}",
							"{TIME}",
							"{ONLINE_PLAYERS}",
							"{MAX_PLAYERS}",
							"{PREFIX}"
						], [
							$player->getName(),
							date(self::FORMATS["date_time"]),
							count($this->core->getServer()->getOnlinePlayers()),
							$this->core->getServer()->getMaxPlayers(),
							$this->core->getPrefix()
						], self::KICKS["whitelisted"]);
					}
					if($user === null) {
						if($server->isWhitelisted()) {
							$player->close(null, $message);
							return;
						}
					} else {
						if($server->isWhitelisted() && !$user->hasPermission("core.network." . $server->getName() . ".whitelist") && !$user->hasPermission("core.network.whitelist")) {
							$player->close(null, $message);
							return;
						}
					}
				} else {
					if($user->loaded()) {
						if(!$user->hasPermission("core.network." . $server->getName() . ".full")) {
							if(!empty(self::KICKS["full"])) {
								$message = str_replace([
									"{PLAYER}",
									"{TIME}",
									"{ONLINE_PLAYERS}",
									"{MAX_PLAYERS}",
									"{PREFIX}"
								], [
									$player->getName(),
									date(self::FORMATS["date_time"]),
									count($this->core->getServer()->getOnlinePlayers()),
									$this->core->getServer()->getMaxPlayers(),
									$this->core->getPrefix()
								], self::KICKS["full"]);

								$player->close(null, $message);
								return;
							}
						}
					}
				}
			});
		}
	}

	public function onPlayerQuit(PlayerQuitEvent $event) {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$message = "";

			if($player->hasPermission("core.broadcast.quit")) {
				if(!empty(self::QUITS["normal"])) {
					$message = str_replace([
						"{PLAYER}",
						"{TIME}",
						"{NAME_TAG_FORMAT}"
					], [
						$player->getName(),
						date(self::FORMATS["date_time"]),
						str_replace("{DISPLAY_NAME}", $player->getName(), $player->getCoreUser()->getRank()->getNameTagFormat())
					], self::QUITS["normal"]);
				}
			}
			$event->setQuitMessage($message);
			$player->leave();
		}
	}

	public function onEntityLevelChange(EntityLevelChangeEvent $event) {
		$entity = $event->getEntity();

		if($entity instanceof CorePlayer) {
			if(!in_array($event->getTarget(), $this->core->getBroadcast()->getBossBar()->getWorlds())) {
				$entity->removeBossBar();
			} else {
				$entity->sendBossBar();
			}
			$origin = $event->getOrigin();
			$target = $event->getTarget();

			if(!empty(self::DIMENSIONS["change"])) {
				$message = str_replace([
					"{PLAYER}",
					"{TIME}",
					"{ORIGIN}",
					"{TARGET}",
					"{NAME_TAG_FORMAT}"
				], [
					$entity->getName(),
					date(self::FORMATS["date_time"]),
					$origin->getName(),
					$target->getName(),
					str_replace("{DISPLAY_NAME}", $entity->getName(), $entity->getCoreUser()->getRank()->getNameTagFormat())
				], self::DIMENSIONS["change"]);

				$this->core->getServer()->broadcastMessage($message);
			}
		}
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $event) {
		$player = $event->getPlayer();
		$pk = $event->getPacket();

		if($player instanceof CorePlayer) {
			switch(true) {
				case $pk instanceof LoginPacket:
					if($pk->protocol < ProtocolInfo::CURRENT_PROTOCOL) {
						if(!empty(self::KICKS["outdated"]["client"])) {
							$message = str_replace(["{PLAYER}", "{TIME}"], [$player->getName(), date(self::FORMATS["date_time"])], self::KICKS["outdated"]["client"]);

							$player->close($message);
							$event->setCancelled(true);
						}
					} else if($pk->protocol > ProtocolInfo::CURRENT_PROTOCOL) {
						if(!empty(self::KICKS["outdated"]["server"])) {
							$message = str_replace(["{PLAYER}", "{TIME}"], [$player->getName(), date(self::FORMATS["date_time"])], self::KICKS["outdated"]["server"]);

							$player->close($message);
							$event->setCancelled(true);
						}
					}
				break;
			}
		}
	}
}