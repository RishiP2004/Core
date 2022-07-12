<?php

declare(strict_types = 1);

namespace core\player;

use core\Core;

use core\anticheat\AntiCheatManager;
use core\broadcast\BroadcastManager;
use core\essence\EssenceManager;
use core\essential\EssentialManager;
use core\network\NetworkManager;
use core\utils\ItemUtils;
use core\broadcast\Broadcasts;
use core\broadcast\bossbar\Messages;
use core\utils\PMUtils;
use core\vote\VoteData;
use core\world\area\Area;
use core\world\WorldManager;

use dktapps\pmforms\ServerSettingsForm;

use pocketmine\lang\{
	Language,
	Translatable
};

use pocketmine\math\Vector3;

use pocketmine\player\Player;

use pocketmine\network\mcpe\protocol\{
	SetPlayerGameTypePacket,
	ServerSettingsResponsePacket
};

use pocketmine\world\format\Chunk;

use pocketmine\utils\TextFormat;

use pocketmine\permission\PermissionAttachment;

use pocketmine\console\ConsoleCommandSender;

use pocketmine\event\player\PlayerKickEvent;

class CorePlayer extends Player {
	private $coreUser;

	const STAFF_CHAT = "staff";
	const NORMAL_CHAT = "normal";
	const VIP_CHAT = "vip"; //faction
	const SCOREBOARD = 0;
	const POPUP = 1;
		
	private array $interacts = [];
	private int $chatTime = 0;
	public int $lastHeldSlot = 0;

	public string $chatType = self::NORMAL_CHAT;
	public bool $fly = false;
	public array $hud = [];
	
	public bool $staffMode = false;

	protected ?int $lastMovement = null;

	public PermissionAttachment $attachment;

	private ?CorePlayer $recentMessager = null;

	public function getCoreUser() : CoreUser {
		if(!$this->isInitialized()) {
			throw new \RuntimeException("Tried to get core user of uninitialized player");
		}
		return $this->coreUser;
	}

	public function join(CoreUser $coreUser) : void {
		if(!$this->isInitialized()) {
			$this->coreUser = $coreUser;
		}
		if($this->isOnline()) {
			$this->attachment = $this->addAttachment(Core::getInstance());
			$this->updatePermissions();
			EssenceManager::getInstance()->spawnNPCs($this);
			EssenceManager::getInstance()->spawnHolograms($this);
			BroadcastManager::getInstance()->getBossBar()->get()->addPlayer($this);
			$this->setBarText();
			WorldManager::getInstance()->players[$this->getName()] = null;
			AntiCheatManager::getInstance()->setWatch($this);

			if($this->getCoreUser()->getName() !== $this->getName()) {
				$this->getCoreUser()->setName($this->getName());
			}
			$this->getCoreUser()->setServer(NetworkManager::getInstance()->getServerFromIp($this->getServer()->getIp()));
			$this->getCoreUser()->save();
			$this->setHud(self::SCOREBOARD);
			$this->setHud(self::POPUP);
			$this->setNameTag($this->getCoreUser()->getRank()->getNameTagFormatFor($this));
			$this->updateArea();
		}
	}

	public function leave() : void {
		EssenceManager::getInstance()->despawnNPCs($this);
		BroadcastManager::getInstance()->getBossBar()->get()->removePlayer($this);

		if(!is_null($this->getAttachment())) {
			$this->removeAttachment($this->getAttachment());
		}
		if($this->isInitialized()) {
			$this->getCoreUser()->setServer(null);
			$this->getCoreUser()->save();
		}
	}

	public function isInitialized() : bool {
		return $this->coreUser instanceof CoreUser;
	}

	public function broadcast(string $broadcast) : string {
		$format = Broadcasts::FORMATS["broadcast"];
		$format = str_replace("{PREFIX}", Core::PREFIX, $format);
		$format = str_replace("{TIME}", date(Broadcasts::FORMATS["date_time"]), $format);
		$format = str_replace("{MESSAGE}", $broadcast, $format);
		$format = str_replace("{SENDER}", $this->getName(), $format);
		return $format;
	}

	public function setBarText() : void {
		$bossBar = BroadcastManager::getInstance()->getBossBar()->get();

		if(!empty(Messages::HEAD_MESSAGE)) {
			$bossBar->setTitle($this->formatBossBar(Messages::HEAD_MESSAGE) . TextFormat::RESET);
		}
		$currentMSG = Messages::CHANGING["messages"][BroadcastManager::getInstance()->getBossBar()->int % count(Messages::CHANGING["messages"])];

		if(strpos($currentMSG, '%') > -1) {
			$percentage = substr($currentMSG, 1, strpos($currentMSG, '%') - 1);

			if(is_numeric($percentage)) {
				$bossBar->setPercentage($percentage / 100);
			}
			$bossBar->setSubTitle(substr($currentMSG, strpos($currentMSG, '%') + 2));
		}
	}

	public function formatBossBar(string $text) : string {
		return str_replace(["{PREFIX}", "{NAME}", "{DISPLAY_NAME}", "{MAX_PLAYERS}", "{ONLINE_PLAYERS}", "{TIME}"], [Core::PREFIX, $this->getName(), $this->getNameTag(), $this->getServer()->getMaxPlayers(), count($this->getServer()->getOnlinePlayers()), date(Broadcasts::FORMATS["date_time"])], $text);
	}

	public function getLastMovement() : ?int {
		return $this->lastMovement;
	}

	public function setLastMovement(?int $time) {
		$this->lastMovement = $time;
	}

	public function getLastChatTime() : int {
		return $this->chatTime;
	}

	public function canChat() : bool {
		if(!$this->hasPermission("player.chat.time")) {
			return time() - $this->chatTime >= $this->getCoreUser()->getRank()->getChatTime() or $this->chatTime === null;
		}
		return true;
	}

	public function setChatTime() : void {
		$this->chatTime = time();
	}

	public function getChatType() : string {
		return $this->chatType;
	}

	public function setChatType(string $chatType) : void {
		$this->chatType = $chatType;
	}

	public function getInteracts() : array {
		return $this->interacts;
	}

	public function addToInteract() : void {
		if(($this->interacts["time"] ?? 0) === time()) {
			$this->interacts["amount"]++;
			return;
		}
		$this->interacts["time"] = time();
		$this->interacts["amount"] = 1;
	}

	public function flying() : bool {
		return $this->fly;
	}
	//todo?
	public function setFly(bool $fly = true) : void {
		$this->fly = $fly;
		$this->setAllowFlight($fly);

		if(!$fly) {
			$this->setFlying(false);
		}
	}

	public function hasHud(int $type) : bool {
		return $this->hud[$type] ?? isset($this->hud[$type]);
	}

	public function setHud(int $type, bool $hud = true) : void {
		$this->hud[$type] = $hud;
		if($hud) {
			$this->getCoreUser()->getServer()->addHud($type, $this);
		} else {
			$this->getCoreUser()->getServer()->removeHud($type, $this);
		}
	}
	//better way?
	public function isInStaffMode() : bool {
		return $this->staffMode;
	}
	
	public function setStaffMode(bool $value) : void {
		if($value) {
			$this->staffMode = true;
			//todo
		} else {
			$this->staffMode = false;
			//todo
		}
	}

	public function getRecentMessager() : ?CorePlayer {
		return $this->recentMessager;
	}

	public function setRecentMessager(CorePlayer $player) : void {
		$this->recentMessager = $player;
	}

	public function getAttachment() : ?PermissionAttachment {
		return $this->attachment;
	}

	public function updatePermissions() : void {
		if(!$this->isInitialized()) {
			return;
		}
		$attachment = $this->getAttachment();
		$attachment->clearPermissions();

		foreach($this->getCoreUser()->getAllPermissions() as $permission) {
			if($permission === "*") {
				foreach(PMUtils::getPocketMinePermissions() as $perm) {
					$attachment->setPermission($perm, true);
				}
			} else if(is_string($permission)) {
				$attachment->setPermission($permission, true);
			}
		}
	}

	public function claimVote() : void {
		$item = ItemUtils::getRandomItems(VoteData::ITEMS);

		if($this->getInventory()->canAddItem($item)) {
			$this->getInventory()->addItem($item);
		} else {
			$this->getWorld()->dropItem($this->getPosition(), $item);
		}
		foreach(VoteData::COMMANDS as $key => $command) {
			$command = str_replace("{PLAYER}", $this->getName(), $command);
			$this->getServer()->getCommandMap()->dispatch(new ConsoleCommandSender(\pocketmine\Server::getInstance(), \pocketmine\Server::getInstance()->getLanguage()), $command);
		}
		$this->sendMessage(Core::PREFIX . "Thanks for Voting!");
		$this->getServer()->broadcastMessage(Core::PREFIX . $this->getName() . " Voted for the Server and got rewarded!");
	}

	public function getArea() : ?Area {
		$area = WorldManager::getInstance()->players[$this->getName()] ?? null;
		return $area !== null ? WorldManager::getInstance()->getArea($area->getName()) : null;
	}

	public function updateArea() : bool {
		$oldArea = $this->getArea();
		$newArea = WorldManager::getInstance()->getAreaFromPosition($this->getPosition());
		
		if(!is_null($newArea)) {
			if($newArea !== $oldArea) {
				WorldManager::getInstance()->players[$this->getName()] = $newArea;
				return $this->areaChange($oldArea, $newArea);
			}
		}
		return false;
	}

	public function areaChange(?Area $oldArea, Area $newArea) : bool {
		if(!is_null($oldArea)) {
			if(!$oldArea->allowedLeave() && $this->hasPermission("world." . $oldArea->getName() . ".leave")) {
				$this->sendMessage(Core::ERROR_PREFIX . "You cannot leave this area");
				return false;
			}
			if(($message = $oldArea->getLeaveNotifications()) !== "") {
				$this->sendMessage($message);
			}
			if(!$oldArea->receiveChat()) {
				unset(WorldManager::getInstance()->muted[$this->getName()]);
			}
			foreach($this->getEffects()->all() as $effect) {
				if($effect->getDuration() >= 999999) {
					$this->getEffects()->remove($effect->getType());
				}
			}
			if($oldArea->getFly() === Area::FLY_SUPERVISED && $this->hasPermission("world." . $newArea->getName() . ".fly")) {
				$this->setAllowFlight(false);

				$pk = new SetPlayerGameTypePacket();
				$pk->gamemode = $this->gamemode->id() & 0x01;
				$this->getNetworkSession()->sendDataPacket($pk);
				$this->setFlying(false);
				//$this->sendSettings();
			}
			if(!$this->hasPermission("world." . $oldArea->getName() . ".whitelist")) {
				if($oldArea->getGamemode() !== ($gamemode = $this->getServer()->getGamemode())) {
					$this->setGamemode($gamemode);

					if($gamemode->id() === 0 or $gamemode->id() === 2) {
						$this->setAllowFlight(false);
						$pk = new SetPlayerGameTypePacket();
						$pk->gamemode = $this->gamemode->id()&0x01;
						$this->getNetworkSession()->sendDataPacket($pk);
						$this->setFlying(false);
						//$this->sendSettings();
					}
				}
			}
		}
		if(!is_null($newArea)) {
			if(!$newArea->allowedEnter() && $this->hasPermission("core.world." . $newArea->getName() . ".enter")) {
				$this->sendMessage(Core::ERROR_PREFIX . "You cannot enter this area");
				return false;
			}
			if(($message = $newArea->getEnterNotifications()) !== "") {
				$this->sendMessage($message);
			}
			if(!$newArea->receiveChat()) {
				WorldManager::getInstance()->muted[$this->getName()] = $this;
			}
			$effects = $newArea->getAreaEffects();

			if(!empty($effects) && $this->hasPermission("core.world." . $newArea->getName() . ".effects")) {
				$this->getEffects()->clear();

				foreach($effects as $effect) {
					$this->getEffects()->add($effect);
				}
			}
			if(!$this->hasPermission("core.world." . $newArea->getName() . ".whitelist")) {
				if(($gamemode = $newArea->getGamemode()) !== $this->getGamemode()) {
					$this->setGamemode($gamemode);
					if($gamemode->id() === 0 or $gamemode->id() === 2) {
						$this->setAllowFlight(false);
						$pk = new SetPlayerGameTypePacket();
						$pk->gamemode = $this->gamemode->id()&0x01;
						$this->getNetworkSession()->sendDataPacket($pk);
						$this->setFlying(false);
						$this->sendSettings();
					}
				}
			}
			if(($flight = $newArea->getFly()) !== $newArea::FLY_VANILLA && $this->hasPermission("core.world." . $newArea->getName() . ".fly")) {
				switch($flight) {
					case $newArea::FLY_ENABLE:
					case $newArea::FLY_SUPERVISED:
						if(!$this->getAllowFlight()) {
							$this->setAllowFlight(true);
						}
					break;
					case $newArea::FLY_DISABLE:
						$this->setAllowFlight(false);

						$pk = new SetPlayerGameTypePacket();
						$pk->gamemode = $this->gamemode->id()&0x01;
						$this->getNetworkSession()->sendDataPacket($pk);
						$this->setFlying(false);
						//$this->sendSettings();
					break;
				}
			}
		}
		return true;
	}

	public function sendSettingForm(ServerSettingsForm $form) {
		$id = $this->formIdCounter++;
		$pk = new ServerSettingsResponsePacket();
		$pk->formId = $id;
		$pk->formData = json_encode($form);

		if($this->getNetworkSession()->sendDataPacket($pk)) {
			$this->forms[$id] = $form;
		}
	}

	public function kick(string $reason = "", Translatable|string|null $quitMessage = null) : bool {
		$event = new PlayerKickEvent($this, $reason, $quitMessage ?? $this->getLeaveMessage());
		$event->call();

		if(!$event->isCancelled()) {
			$reason = $event->getReason();
			$message = $reason;
			//todo fix up msgs
			if($reason = "ban") {
				if(!EssentialManager::getInstance()->getNameBans()->isBanned($this->getName()) or !EssentialManager::getInstance()->getIpBans()->isBanned($this->getName())) {
					$message = Core::PREFIX . "You have been Kicked\n" . TextFormat::GRAY . ($reason !== "" ? " Reason: " . $reason : "");
				}
			} else {
				if($reason === "") {
					$message =Core::PREFIX . "You have been Kicked";
				}
			}
			$this->disconnect($reason, $message);
			return true;
		}
		return false;
	}

	public function onChunkPopulated(int $chunkX, int $chunkZ, Chunk $chunk) : void {
		parent::onChunkPopulated($chunkX, $chunkZ, $chunk);
	}

	public function sendMessage(Translatable|string $message) : void {
		parent::sendMessage($message);
	}

	public function getLastPlayed() : ?int {
		return parent::getLastPlayed();
	}

	public function getScreenLineHeight() : int {
		return parent::getScreenLineHeight();
	}

	public function onChunkLoaded(int $chunkX, int $chunkZ, Chunk $chunk) : void {
		parent::onChunkLoaded($chunkX, $chunkZ, $chunk);
	}

	public function onChunkChanged(int $chunkX, int $chunkZ, Chunk $chunk) : void {
		parent::onChunkChanged($chunkX, $chunkZ, $chunk);
	}

	public function getLanguage() : Language {
		return parent::getLanguage();
	}

	public function hasPlayedBefore() : bool {
		return parent::hasPlayedBefore();
	}

	public function onBlockChanged(Vector3 $block) : void {
		parent::onBlockChanged($block);
	}

	public function getServer() : \pocketmine\Server {
		return parent::getServer();
	}

	public function onChunkUnloaded(int $chunkX, int $chunkZ, Chunk $chunk) : void {
		parent::onChunkUnloaded($chunkX, $chunkZ, $chunk);
	}

	public function getFirstPlayed() : ?int {
		return parent::getFirstPlayed();
	}

	public function setScreenLineHeight(?int $height) : void {
		parent::setScreenLineHeight($height);
	}
}