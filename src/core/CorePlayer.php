<?php

namespace core;

use core\utils\Item;

use core\essence\npc\NPC;

use core\mcpe\network\PlayerNetworkSessionAdapter;

use core\mcpe\form\{
    Form,
    MenuForm,
    CustomForm
};

use core\mcpe\form\element\{
	Button,
	Image
};
use core\mcpe\entity\{
	CreatureBase,
	Interactable
};

use core\mcpe\entity\projectile\FishingHook;

use core\network\server\Server;

use core\stats\task\{
    PlayerJoin,
    AFKKick
};

use core\world\area\Area;

use pocketmine\network\SourceInterface;

use pocketmine\Player;

use pocketmine\network\mcpe\protocol\{
    AddEntityPacket,
    BossEventPacket,
	EntityEventPacket,
    SetPlayerGameTypePacket,
    EntityPickRequestPacket,
    InteractPacket,
	PlayerInputPacket,
    InventoryTransactionPacket,
    ServerSettingsResponsePacket
};

use pocketmine\entity\Entity;

use pocketmine\level\Level;

use pocketmine\utils\TextFormat;

use pocketmine\permission\PermissionAttachment;

use pocketmine\command\ConsoleCommandSender;

use pocketmine\event\player\PlayerKickEvent;

abstract class CorePlayer extends Player {
    /**
     * @var \core\Core
     */
    private $core;

    private $interacts = [], $attachments = [];

    private $chatTime = 0;

    private $AFK = false, $fishing = false;
    /** @var null | FishingHook */
    public $fishingHook = null;
	/**
	 * @var int|null
	 */
	protected $lastMovement = null, $kickAFK = null;

    public function __construct(SourceInterface $interface, string $ip, int $port) {
		parent::__construct($interface, $ip, $port);

		$this->sessionAdapter = new PlayerNetworkSessionAdapter($this->server, $this);
	}

	public function __destruct() {
		$this->setFishing(false);
	}

	public function setCore(Core $core) {
        $this->core = $core;
    }

    public function getCoreUser() : CoreUser {
        return $this->core->getStats()->getCoreUser($this->getName()) ?? $this->core->getStats()->getCoreUserXuid($this->getXuid());
    }

    public function join() {
        $this->setNameTag($this->getNameTagFormat());
        $this->attach();
        $this->updatePermissions();
		$this->spawnNPCs();
        $this->spawnFloatingTexts();
        $this->getCoreUser()->setServer($this->core->getNetwork()->getServerFromIp($this->core->getServer()->getIp()));
        $this->core->getScheduler()->scheduleDelayedTask(new PlayerJoin($this->core, $this), 20);
    }

    public function leave() {
		$this->despawnNPCs();
        $this->detach();
        $this->getCoreUser()->setServer(null);
        $this->getCoreUser()->save();
    }

    public function broadcast(string $broadcast) : string {
        $format = $this->core->getBroadcast()->getFormats("broadcast");
        $format = str_replace("{PREFIX}", $this->core->getPrefix(), $format);
        $format = str_replace("{TIME}", date($this->core->getBroadcast()->getFormats("date_time")), $format);
        $format = str_replace("{MESSAGE}", $broadcast, $format);
        $format = str_replace("{SENDER}", $this->getName(), $format);
        return $format;
    }

    public function sendBossBar(int $eid, string $title) {
        $this->core->getBroadcast()->getBossBar()->remove([$this], $eid);

        $pk = new AddEntityPacket();
        $pk->entityRuntimeId = $eid;
        $pk->type = $this->core->getBroadcast()->getBossBar()->getEntity();
        $pk->position = $this->getPosition()->asVector3()->subtract(0, 28);
        $pk->metadata = [
            Entity::DATA_LEAD_HOLDER_EID => [
                Entity::DATA_TYPE_LONG, -1
            ],
            Entity::DATA_FLAGS => [
                Entity::DATA_TYPE_LONG, 0 ^ 1 << Entity::DATA_FLAG_SILENT ^ 1 << Entity::DATA_FLAG_INVISIBLE ^ 1 << Entity::DATA_FLAG_NO_AI
            ],
            Entity::DATA_SCALE => [
                Entity::DATA_TYPE_FLOAT, 0
            ],
            Entity::DATA_NAMETAG => [
                Entity::DATA_TYPE_STRING, $title
            ],
            Entity::DATA_BOUNDING_BOX_WIDTH => [
                Entity::DATA_TYPE_FLOAT, 0
            ],
            Entity::DATA_BOUNDING_BOX_HEIGHT => [
                Entity::DATA_TYPE_FLOAT, 0
            ]
        ];

        $this->sendDataPacket($pk);

        $bpk = new BossEventPacket();
        $bpk->bossEid = $eid;
        $bpk->eventType = BossEventPacket::TYPE_SHOW;
        $bpk->title = $title;
        $bpk->healthPercent = 1;
        $bpk->unknownShort = 0;
        $bpk->color = 0;
        $bpk->overlay = 0;
        $bpk->playerEid = 0;

        $this->sendDataPacket($bpk);
    }

    public function getBossBarText() : string {
        $text = "";
        $bossBar = $this->core->getBroadcast()->getBossBar();

        if(!empty($bossBar->getHeadMessage())) {
            $text .= $this->formatBossBar($bossBar->getHeadMessage()) . "\n" . "\n" . TextFormat::RESET;
        }
        $currentMSG = $this->core->getBroadcast()->getBossBar()->getChanging("messages")[$bossBar->int % count($bossBar->getChanging("messages"))];

        if(strpos($currentMSG, '%') > -1) {
            $percentage = substr($currentMSG, 1, strpos($currentMSG, '%') - 1);

            if(is_numeric($percentage)) {
                $bossBar->setPercentage(intval($percentage) + 0.5, $bossBar->entityRuntimeId);
            }
            $currentMSG = substr($currentMSG, strpos($currentMSG, '%') + 2);
        }
        $text .= $this->formatBossBar((string) $currentMSG);
        return mb_convert_encoding($text, "UTF-8");
    }

    public function formatBossBar(string $text) : string {
        $text = str_replace([
            "{PREFIX}",
            "{NAME}",
            "{DISPLAY_NAME}",
            "{MAX_PLAYERS}",
            "{ONLINE_PLAYERS}",
            "{TIME}"
        ], [
            $this->core->getPrefix(),
            $this->getName(),
            $this->getDisplayName(),
            $this->core->getServer()->getMaxPlayers(),
            count($this->creationTime->getServer()->getOnlinePlayers()),
            date($this->core->getBroadcast()->getFormats("date_time"))
        ], $text);
        return $text;
    }

    public function checkFloatingTextsLevelChange(Level $level) {
        foreach($this->core->getEssence()->getFloatingTexts() as $floatingText) {
            if(!$floatingText->getPosition()->getLevel()->getName() === $level->getName()) {
                return;
            } else {
                $floatingText->spawnTo($this);
            }
        }
    }

    public function spawnFloatingTexts() {
        foreach($this->core->getEssence()->getFloatingTexts() as $floatingText) {
            $floatingText->spawnTo($this);
        }
    }

    public function areNPCSSpawned() : bool {
        foreach($this->core->getEssence()->getNPCs() as $NPC) {
            return $NPC->isSpawnedTo($this);
        }
        return false;
    }

    public function checkNPCLevelChange(Level $level) {
        foreach($this->core->getEssence()->getNPCs() as $NPC) {
            if($NPC instanceof NPC) {
                if($NPC->getPosition()->getLevel()->getName() === $level->getName()) {
                    $NPC->spawnTo($this);
                } else {
                    $NPC->despawnFrom($this);
                }
            }
        }
    }

    public function spawnNPCs() {
        foreach($this->core->getEssence()->getNPCs() as $NPC) {
            if($NPC instanceof NPC) {
                $NPC->spawnTo($this);
            }
        }
    }

    public function despawnNPCs() {
        foreach($this->core->getEssence()->getNPCs() as $NPC) {
            if($NPC instanceof NPC) {
                $NPC->despawnFrom($this);
            }
        }
    }

    public function rotateNPCs() {
        foreach($this->core->getEssence()->getNPCs() as $NPC) {
            if($NPC instanceof NPC) {
                if($NPC->rotate()) {
                    $NPC->rotateTo($this);
                }
            }
        }
    }

    public function moveNPCs() {
        foreach($this->core->getEssence()->getNPCs() as $NPC) {
            if($NPC instanceof NPC) {
                $NPC->move($this);
            }
        }
    }

    public abstract function getNameTagFormat() : string;

    public abstract function getChatFormat(string $message) : string;

    public function isAFK() : bool {
        return $this->AFK;
    }

    public function setAFK(bool $AFK) {
        $time = $this->core->getStats()->getAFKAutoKick();

        if(!$AFK && ($id = $this->getAFKKickTaskID()) !== null) {
            $this->core->getScheduler()->cancelTask($id);
            $this->setAFKKickTaskId(null);
        } else if($AFK && (is_int($time) && $time > 0) && !$this->hasPermission("core.stats.afk.kick")) {
            $task = $this->core->getScheduler()->scheduleDelayedTask(new AFKKick($this->core, $this), $time * 20);

            $this->setAFKKickTaskID($task->getTaskId());
        }
        $this->AFK = $AFK;

        $this->sendMessage($this->core->getPrefix() . "You are " . ($this->isAFK() ? "now" : "no longer") . " AFK");
    }

    public function getAFKKickTaskId() : ?int {
        if(!$this->isAFK()) {
            return null;
        }
        return $this->kickAFK;
    }

    public function setAFKKickTaskId(?int $id) {
        $this->kickAFK = $id;
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
        if(!$this->hasPermission("core.stats.chat.time")) {
            return time() - $this->chatTime >= $this->getCoreUser()->getRank()->getChatTime() or $this->chatTime === null;
        }
        return true;
    }

    public function setChatTime() {
        $this->chatTime = time();
    }

    public function addToInteract() {
        if($this->interacts["time"] === time()) {
            $this->interacts["amount"]++;
            return;
        }
        $this->interacts["time"] = time();
        $this->interacts["amount"] = 1;
        return;
    }

    public function getInteracts() : array {
        return $this->interacts;
    }

    public function isFishing() : bool {
        return $this->fishing;
    }

    public function setFishing(bool $fishing) {
        $this->fishing = $fishing;

        if(!$fishing) {
			if($this->fishingHook instanceof FishingHook) {
				$this->fishingHook->broadcastEntityEvent(EntityEventPacket::FISH_HOOK_TEASE, null, $this->fishingHook->getViewers());

				if(!$this->fishingHook->isFlaggedForDespawn()) {
					$this->fishingHook->flagForDespawn();
				}
				$this->fishingHook = null;
			}
		}
    }

    public function getAttachment() : PermissionAttachment {
        return $this->attachments[$this->getName()];
    }

    public function attach() {
        $attachment = $this->addAttachment($this->core);
        $this->attachments[$this->getName()] = $attachment;
    }

    public function detach() {
        $this->removeAttachment($this->attachments[$this->getName()]);
        unset($this->attachments);
    }

    public function updatePermissions() {
        $permissions = [];

        foreach($this->getCoreUser()->getAllPermissions() as $permission) {
            if($permission === "*") {
                foreach($this->getServer()->getPluginManager()->getPermissions() as $temp) {
                    $permissions[$temp->getName()] = true;
                }
            } else {
                $isNegative = substr($permission, 0, 1) === "-";

                if($isNegative) {
                    $permission = substr($permission, 1);
                }
                $permissions[$permission] = !$isNegative;
            }
        }
        $attachment = $this->getAttachment();

        $attachment->clearPermissions();
        $attachment->setPermissions($permissions);
    }

    public function sendServerSelectorForm() {
        $this->sendMessage($this->core->getPrefix() . "Opened Servers menu");

        $options = [];

        foreach($this->core->getNetwork()->getServers() as $server) {
            if($server instanceof Server) {
                if(!$server->isOnline()) {
                    $onlinePlayers = "";
                    $maxSlots = "";
                    $online = "No";

                    if($server->isWhitelisted()) {
                        $online = "Whitelisted";
                    }
                } else {
                    $onlinePlayers = "Players: " . count($server->getOnlinePlayers()) . "/";
                    $maxSlots = $server->getMaxSlots();
                    $online = "Yes";
                }
                $name = TextFormat::GRAY . $server->getName() . "\n" . TextFormat::GRAY . "Online: " . $online . "\n" . TextFormat::GRAY . $onlinePlayers . $maxSlots;

                if(empty($server->getIcon())) {
                    $b1 = new Button($name);

                    $b1->setId($server->getName());

                    $options[] = $b1;
                }
				$b2 = new Button($name, new Image($server->getIcon(), Image::TYPE_URL));

				$b2->setId($server->getName());

                $options[] = $b2;
            }
        }
        $this->sendForm(new class(TextFormat::GOLD . "Server", TextFormat::LIGHT_PURPLE . "Pick a Server", $options) extends MenuForm {
            public function __construct(string $title, string $text, array $options) {
                parent::__construct($title, $text, $options);
            }

            public function onSubmit(Player $player, Button $selectedOption) : void {;
                if($player instanceof CorePlayer) {
                    $server = Core::getInstance()->getNetwork()->getServer($selectedOption->getId());

                    if($server instanceof Server) {
                        if(!$player->hasPermission("core.network." . $server->getName())) {
                            $player->sendMessage(Core::getInstance()->getErrorPrefix() . "You do not have Permission to use this Server");
                        }
                        if($server->isWhitelisted() && !$player->hasPermission("core.network." . $server->getName() . ".whitelist")) {
                            $player->sendMessage(Core::getInstance()->getErrorPrefix() . $server->getName() . " is Whitelisted");
                        } else {
                            $player->transfer($server->getIp() . $server->getPort());
                            $player->sendMessage(Core::getInstance()->getErrorPrefix() . "Transferring to the Server " . $server->getName());
                        }
                    }
                }
            }

            public function onClose(Player $player) : void {
                $player->sendMessage(Core::getInstance()->getPrefix() . "Closed Servers menu");
            }
        });
    }

    public function sendProfileForm(string $key = "profile", CoreUser $user = null) {
        switch($key) {
            case "profile":
                $this->sendMessage($this->core->getPrefix() . "Opened Profile menu");

                $b1 = new Button(TextFormat::GRAY . "Global");

                $b1->setId(1);

                $b2 = new Button("Lobby", new Image($this->core->getNetwork()->getServer("Lobby")->getIcon(), Image::TYPE_URL));

                $b2->setId(2);

                $b3 = new Button("Factions", new Image($this->core->getNetwork()->getServer("Factions")->getIcon()));

                $b3->setId(3);

                $options = [
                	$b1,
					$b2,
					$b3
				];
                $this->sendForm(new class(TextFormat::GOLD . $user = null ? $user->getName() . "'s Profile" : "Your Profile", TextFormat::GRAY . "Select an Option", $options, $user) extends MenuForm {
                    private $user;

                    public function __construct($title, $text, $options, $user) {
                        parent::__construct($title, $text, $options);

                        $this->user = $user;
                    }

                    public function onSubmit(Player $player, Button $selectedOption) : void {
                        if($player instanceof CorePlayer) {
                            switch($selectedOption->getId()) {
                                case "Global":
                                    $player->sendProfileForm("Global", $this->user);
                                break;
                                case "Lobby":
                                    $player->sendProfileForm("Lobby", $this->user);
                                break;
                                case "Factions":
                                    $player->sendProfileForm("Factions", $this->user);
                                break;
                                default:
                                    $player->sendMessage(Core::getInstance()->getErrorPrefix() . "Must choose a Server");
                                break;
                            }
                        }
                    }

                    public function onClose(Player $player) : void {
                        $player->sendMessage(Core::getInstance()->getPrefix() . "Closed Profile menu");
                    }
                });
            break;
            case "global":
                $server = "";

                if($user instanceof CoreUser) {
                    $server = $user->getServer()->getName();
                }
                $data = [
                    "Coins" => $user->getCoins(),
                    "Rank" => $user->getRank()->getName(),
                    "Permissions" => implode(", ", $user->getPermissions()),
                    "Server" => $server
                ];
                $profile = $user = null ? $user->getName() . "'s Profile" : "Your Profile";

                $this->sendForm(new class(TextFormat::GOLD . $profile . TextFormat::BLUE . "Global", $data, $user) extends CustomForm {
                    private $user;

                    public function __construct(string $title, array $elements, \Closure $onSubmit, ?\Closure $onClose = null) {
						parent::__construct($title, $elements, $onSubmit, $onClose);
					}

					public function onClose(Player $player) : void {
                        $player->sendMessage(Core::getInstance()->getPrefix() . "Closed Profile menu");
                    }
                });
            break;
        }
    }

    public function giveVoteRewards(int $multiplier) {
        if($multiplier > 1) {
            $this->sendMessage($this->core->getErrorPrefix() . "You haven't voted on any server lists");
            return;
        }
        if($this->core->getNetwork()->getServerFromIp($this->getServer()->getIp())->getName() === "Factions") {
            for($r = 0; $r < $this->core->getVote()->getItems() * $multiplier; $r++) {
                $this->getInventory()->addItem(Item::getRandomItems($this->core->getVote()->getItems()) * $multiplier);
            }
        }
        foreach($this->core->getVote()->getCommands() as $key => $command) {
            $command = str_replace("{PLAYER}", $this->getName(), $command);
            $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $command * $multiplier);
        }
    }

    public function getArea() : ?Area {
        return $this->core->getWorld()->getArea($this->core->getWorld()->players[$this->getName()]) ?? null;
    }

    public function updateArea() : bool {
        $oldArea = $this->core->getWorld()->players[$this->getName()];

        if(($newArea = $this->core->getWorld()->getAreaFromPosition($this->getPosition())) !== $oldArea) {
            $this->core->getWorld()->players[$this->getName()] = $newArea;

            return $this->areaChange($oldArea, $newArea);
        }
        return true;
    }

    public function areaChange(string $oldArea, string $newArea) : bool {
        if($oldArea !== "") {
            $oldArea = $this->core->getWorld()->getArea($oldArea);

            if($oldArea->allowedLeave()) {
                $this->sendMessage($this->core->getErrorPrefix() . "You cannot leave this area");
                return false;
            }
            if($message = $oldArea->getLeaveNotifications() !== "") {
                $this->sendMessage($message);
            }
            if(!$oldArea->receiveChat()) {
                unset($this->core->getWorld()->muted[$this->getName()]);
            }
            foreach($this->getEffects() as $effect) {
                if($effect->getDuration() >= 999999) {
                    $this->removeEffect($effect->getId());
                }
            }
            if($oldArea->getFly() === Area::FLY_SUPERVISED) {
                $this->setAllowFlight(false);

                $pk = new SetPlayerGameTypePacket();
                $pk->gamemode = $this->gamemode & 0x01;

                $this->sendDataPacket($pk);
                $this->setFlying(false);
                $this->sendSettings();
            }
            if(!$this->hasPermission("core.world." . $oldArea->getName())) {
                if($oldArea->getGamemode() !== ($gamemode = $this->getServer()->getDefaultGamemode())) {
                    $this->setGamemode($gamemode);

                    if($gamemode === 0 or $gamemode === 2) {
                        $this->setAllowFlight(false);

                        $pk = new SetPlayerGameTypePacket();
                        $pk->gamemode = $this->gamemode & 0x01;

                        $this->sendDataPacket($pk);
                        $this->setFlying(false);
                        $this->sendSettings();
                    }
                }
            }
        }
        if($newArea !== "") {
            $newArea = $this->core->getWorld()->getArea($newArea);

            if(!$newArea->allowedEnter()) {
                $this->sendMessage($this->core->getErrorPrefix() . "You cannot enter this area");
                return false;
            }
            if($message = $oldArea->getEnterNotifications() !== "") {
                $this->sendMessage($message);
            }
            if(!$oldArea->receiveChat()) {
                $this->core->getWorld()->muted[$this->getName()] = $this;
            }
            $effects = $newArea->getAreaEffects();

            if(!empty($effects)) {
                $this->removeAllEffects();

                foreach($effects as $effect) {
                    $this->addEffect($effect);
                }
            }
            if(!$this->hasPermission("core.world." . $newArea->getName())) {
                if(($gamemode = $newArea->getGamemode()) !== $this->getGamemode()) {
                    $this->setGamemode($gamemode);

                    if($gamemode === 0 or $gamemode === 2) {
                        $this->setAllowFlight(false);

                        $pk = new SetPlayerGameTypePacket();
                        $pk->gamemode = $this->gamemode & 0x01;

                        $this->sendDataPacket($pk);
                        $this->setFlying(false);
                        $this->sendSettings();
                    }
                }
            }
            if(($flight = $newArea->getFly()) !== $newArea::FLY_VANILLA) {
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
                        $pk->gamemode = $this->gamemode & 0x01;

                        $this->sendDataPacket($pk);
                        $this->setFlying(false);
                        $this->sendSettings();
                    break;
                }
            }
        }
        return true;
    }

    public function sendSetting(Form $form) {
        $reflection = new \ReflectionObject($this);

        $idProperty = $reflection->getProperty("formIdCounter");

        $idProperty->setAccessible(true);

        $idPropertyValue = $idProperty->getValue($this);
        /** @noinspection PhpUnusedLocalVariableInspection */
        $id = $idPropertyValue++;

        $idProperty->setValue($this, $id);

        $pk = new ServerSettingsResponsePacket();
        $pk->formId = $id;
        $pk->formData = json_encode($form);

        if($this->sendDataPacket($pk)) {
            $formsProperty = $reflection->getProperty("forms");

            $formsProperty->setAccessible(true);

            $formsValue = $formsProperty->getValue($this);
            $formsValue[$id] = $form;

            $formsProperty->setValue($this, $formsValue);
        }
    }

    public function kick(string $reason = "", bool $isAdmin = true, $quitMessage = null) : bool {
        $this->server->getPluginManager()->callEvent($event = new PlayerKickEvent($this, $reason, $quitMessage ?? $this->getLeaveMessage()));

        if(!$event->isCancelled()) {
            $reason = $event->getReason();
            $message = $reason;

            if($isAdmin) {
                if(!$this->isBanned()) {
                    $message = $this->core->getPrefix() . "You have been Kicked\n" . TextFormat::GRAY . ($reason !== "" ? " Reason: " . $reason : "");
                }
            } else {
                if($reason === "") {
                    $message = $this->core->getPrefix() . "You have been Kicked";
                }
            }
            $this->close($event->getQuitMessage(), $message);
            return true;
        }
        return false;
    }

	public function handlePlayerInput(PlayerInputPacket $packet) : bool {
		return false; // TODO
	}

    public function handleEntityPickRequest(EntityPickRequestPacket $pk) : bool {
        $target = $this->level->getEntity($pk->entityUniqueId);

        if($target === null) {
			return false;
		}
        if($this->isCreative()) {
            $item = Item::get(Item::MONSTER_EGG, $target::NETWORK_ID, 64);

            if(!empty($target->getNameTag())) {
                $item->setCustomName($target->getNameTag());
            }
            $this->getInventory()->setItem($pk->hotbarSlot, $item);
        }
        return true;
    }

    public function handleInteract(InteractPacket $pk) : bool {
        $return = parent::handleInteract($pk);

        switch($pk->action) {
            case InteractPacket::ACTION_LEAVE_VEHICLE:
                // TODO: entity linking
            break;
            case InteractPacket::ACTION_MOUSEOVER:
                $target = $this->level->getEntity($pk->target);

                $this->setTargetEntity($target);

                if($target instanceof CreatureBase) {
                    // TODO: check player looking at head and if wearing jack 'o lantern
                    $target->onPlayerLook($this);
                } else if($target === null) {
					$this->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, "");
				}
                $return = true;
            break;
            default:
                $this->server->getLogger()->debug("Unhandled/unknown interaction type " . $pk->action . "received from " . $this->getName());
                $return = false;
        }
        return $return;
    }

    public function handleInventoryTransaction(InventoryTransactionPacket $pk) : bool {
        $return = parent::handleInventoryTransaction($pk);

        if($pk->transactionType === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY) {
            $type = $pk->trData->actionType;

            switch($type) {
                case InventoryTransactionPacket::USE_ITEM_ON_ENTITY_ACTION_INTERACT:
                    $target = $this->level->getEntity($pk->trData->entityRuntimeId);

                    $this->setTargetEntity($target);
                    $this->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, "");

                    if($target instanceof Interactable) {
                        $target->onPlayerInteract($this);
                        return true;
                        break;
                    }
                break;
            }
        }
        return $return;
    }
}