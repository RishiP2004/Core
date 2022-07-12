<?php

declare(strict_types = 1);

namespace core\broadcast;

use core\Core;

use core\player\{
	CorePlayer,
	CoreUser
};

use core\network\NetworkManager;

use core\player\PlayerManager;

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
	EntityTeleportEvent
};

use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\network\mcpe\protocol\{
	LoginPacket,
	ProtocolInfo
};

use pocketmine\entity\Living;

use pocketmine\Server;

class BroadcastListener implements Listener {
	public function __construct(private BroadcastManager $manager) {}

	public function onPlayerDeath(PlayerDeathEvent $event) : void {
		$player = $event->getEntity();

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
			if(!empty($this->manager::DEATHS[$stringCause])) {
				$message = $this->manager::DEATHS[$stringCause];

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

	public function onPlayerJoin(PlayerJoinEvent $event) : void {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$message = "";

			if(!$player->hasPlayedBefore()) {
				if(!empty($this->manager::JOINS["first"])) {
					$rank = $player->getCoreUser()->getRank()->getNameTagFormat();

					$message = str_replace([
						"{PLAYER}",
						"{TIME}",
						"{NAME_TAG_FORMAT}"
					], [
						$player->getName(),
						date($this->manager::FORMATS["date_time"]),
						str_replace("{DISPLAY_NAME}", $player->getName(), $rank)
					], $this->manager::JOINS["first"]);
				}
			} else {
				if($player->hasPermission("core.broadcast.join")) {
					if(!empty($this->manager::JOINS["normal"])) {
						$message = str_replace([
							"{PLAYER}",
							"{TIME}",
							"{NAME_TAG_FORMAT}"
						], [
							$player->getName(),
							date($this->manager::FORMATS["date_time"]),
							str_replace("{DISPLAY_NAME}", $player->getName(), $player->getCoreUser()->getRank()->getNameTagFormat())
						], $this->manager::JOINS["normal"]);
					}
				}
			}
			$event->setJoinMessage($message);
		}
	}

	public function onPlayerPreLogin(PlayerPreLoginEvent $event) : void {
		$playerInfo = $event->getPlayerInfo();

		PlayerManager::getInstance()->getCoreUser($playerInfo->getUsername(), function(?CoreUser $user) use($playerInfo, $event) {
			$message = "";

			$server = NetworkManager::getInstance()->getServerFromIp(Server::getInstance()->getIp());

			if(count(Server::getInstance()->getOnlinePlayers()) - 1 < Server::getInstance()->getMaxPlayers()) {
				if(!empty($this->manager::KICKS["whitelisted"])) {
					$message = str_replace([
						"{PLAYER}",
						"{TIME}",
						"{ONLINE_PLAYERS}",
						"{MAX_PLAYERS}",
						"{PREFIX}"
					], [
						$playerInfo->getUsername(),
						date($this->manager::FORMATS["date_time"]),
						count(Server::getInstance()->getOnlinePlayers()),
						Server::getInstance()->getMaxPlayers(),
						Core::PREFIX
					], $this->manager::KICKS["whitelisted"]);
				}
				if($user === null) {
					if($server->isWhitelisted()) {
						$event->setKickReason($event::KICK_REASON_SERVER_WHITELISTED, $message);
					}
				} else {
					if($server->isWhitelisted() && !$user->hasPermission("network." . $server->getName() . ".whitelist") && !$user->hasPermission("network.whitelist")) {
						$event->setKickReason($event::KICK_REASON_SERVER_WHITELISTED, $message);
					}
				}
			} else {
				if($user->loaded()) {
					if(!$user->hasPermission("network." . $server->getName() . ".full")) {
						if(!empty($this->manager::KICKS["full"])) {
							$message = str_replace([
								"{PLAYER}",
								"{TIME}",
								"{ONLINE_PLAYERS}",
								"{MAX_PLAYERS}",
								"{PREFIX}"
							], [
								$playerInfo->getUsername(),
								date($this->manager::FORMATS["date_time"]),
								count(Server::getInstance()->getOnlinePlayers()),
								Server::getInstance()->getMaxPlayers(),
								Core::PREFIX
							], $this->manager::KICKS["full"]);

							$event->setKickReason($event::KICK_REASON_SERVER_FULL, $message);
						}
					}
				}
			}
		});
	}

	public function onPlayerQuit(PlayerQuitEvent $event) : void {
		$player = $event->getPlayer();

		if($player instanceof CorePlayer) {
			$message = "";

			if($player->hasPermission("core.broadcast.quit")) {
				if(!empty($this->manager::QUITS["normal"])) {
					$message = str_replace([
						"{PLAYER}",
						"{TIME}",
						"{NAME_TAG_FORMAT}"
					], [
						$player->getName(),
						date($this->manager::FORMATS["date_time"]),
						str_replace("{DISPLAY_NAME}", $player->getName(), $player->getCoreUser()->getRank()->getNameTagFormat())
					], $this->manager::QUITS["normal"]);
				}
			}
			$event->setQuitMessage($message);
			$player->leave();
		}
	}

	public function onEntityLevelChange(EntityTeleportEvent $event) : void {
		$entity = $event->getEntity();

		if($entity instanceof CorePlayer) {
			if(!in_array($event->getTo()->getWorld(), $this->manager->getBossBar()->getWorlds())) {
				BroadcastManager::getInstance()->getBossBar()->get()->addPlayer($entity);
				$entity->setBarText();
			} else {
				BroadcastManager::getInstance()->getBossBar()->get()->removePlayer($entity);
			}
			$origin = $event->getFrom();
			$target = $event->getTo();

			if(!empty($this->manager::DIMENSIONS["change"])) {
				$message = str_replace([
					"{PLAYER}",
					"{TIME}",
					"{ORIGIN}",
					"{TARGET}",
					"{NAME_TAG_FORMAT}"
				], [
					$entity->getName(),
					date($this->manager::FORMATS["date_time"]),
					$origin->getWorld()->getFolderName(),
					$target->getWorld()->getFolderName(),
					str_replace("{DISPLAY_NAME}", $entity->getName(), $entity->getCoreUser()->getRank()->getNameTagFormat())
				], $this->manager::DIMENSIONS["change"]);

				Server::getInstance()->broadcastMessage($message);
			}
		}
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $event) : void {
		$player = $event->getOrigin();
		$pk = $event->getPacket();

		if($player instanceof CorePlayer) {
			if($pk->pid() == LoginPacket::NETWORK_ID) {
				if($pk->protocol < ProtocolInfo::CURRENT_PROTOCOL) {
					if(!empty($this->manager::KICKS["outdated"]["client"])) {
						$message = str_replace(["{PLAYER}", "{TIME}"], [$player->getName(), date($this->manager::FORMATS["date_time"])], $this->manager::KICKS["outdated"]["client"]);
						//todo check
						$player->close($message);
						$event->cancel();
					}
				} else if($pk->protocol > ProtocolInfo::CURRENT_PROTOCOL) {
					if(!empty($this->manager::KICKS["outdated"]["server"])) {
						$message = str_replace(["{PLAYER}", "{TIME}"], [$player->getName(), date($this->manager::FORMATS["date_time"])], $this->manager::KICKS["outdated"]["server"]);

						$player->close($message);
						$event->cancel();
					}
				}
			}
		}
	}
}