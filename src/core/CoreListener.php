<?php

namespace core;

use core\broadcast\bossbar\Messages;

use core\broadcast\Broadcasts;

use core\utils\Entity;

use core\mcpe\entity\{
    AnimalBase,
    MonsterBase,
    CreatureBase
};

use core\utils\Math;

use core\mcpe\event\ServerSettingsRequestEvent;

use pocketmine\event\Listener;

use pocketmine\event\player\{
    PlayerBedEnterEvent,
    PlayerCreationEvent,
    PlayerChatEvent,
    PlayerCommandPreprocessEvent,
    PlayerDropItemEvent,
    PlayerDeathEvent,
    PlayerExhaustEvent,
    PlayerInteractEvent,
    PlayerItemHeldEvent,
    PlayerJoinEvent,
    PlayerLoginEvent,
    PlayerMoveEvent,
    PlayerPreLoginEvent,
    PlayerQuitEvent
};

use pocketmine\event\entity\{
    EntityDamageEvent,
    EntityDamageByEntityEvent,
    EntityDamageByBlockEvent,
    EntityDeathEvent,
    EntityLevelChangeEvent,
    EntityExplodeEvent,
    ProjectileLaunchEvent
};

use pocketmine\event\block\{
    BlockBreakEvent,
    BlockPlaceEvent
};

use pocketmine\event\server\{
    DataPacketReceiveEvent,
    QueryRegenerateEvent
};

use pocketmine\event\inventory\{
    InventoryPickupItemEvent,
    InventoryPickupArrowEvent,
    InventoryTransactionEvent
};

use pocketmine\event\level\{
    ChunkLoadEvent,
    ChunkUnloadEvent
};

use pocketmine\entity\Living;

use pocketmine\network\mcpe\protocol\{
    LoginPacket,
    ProtocolInfo,
    InventoryTransactionPacket,
    ServerSettingsRequestPacket
};

use pocketmine\inventory\transaction\action\SlotChangeAction;

use pocketmine\inventory\{
    PlayerInventory,
    PlayerCursorInventory
};

use pocketmine\math\Vector3;

use pocketmine\level\Position;

use const pocketmine\IS_DEVELOPMENT_BUILD;

class CoreListener implements Listener {
    private $core;

    public function __construct(Core $core) {
        $this->core = $core;
    }

    public function onPlayerBedEnter(PlayerBedEnterEvent $event) {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
            $area = $player->getArea();

            if($area->getName() !== "") {
                if(!$player->hasPermission("core.world.area.playerbedenter")) {
                    if(!$area->sleep()) {
                        $player->sendMessage($this->core->getErrorPrefix() . "You cannot Sleep in the Area: " . $area->getName());
                        $event->setCancelled();
                    }
                }
            }
        }
    }

    public function onPlayerCreation(PlayerCreationEvent $event) {
        $event->setPlayerClass(CorePlayer::class);
    }

    public function onPlayerChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
            $muteList = $this->core->getEssentials()->getNameMutes();
            $ipMuteList = $this->core->getEssentials()->getIpMutes();

            if($muteList->isBanned($player)) {
                $entries = $muteList->getEntries();
                $entry = $entries[strtolower($player->getName())];
                $reason = $entry->getReason();

                if($entry->getExpires() === null) {
                    if($reason !== null or $reason !== "") {
                        $muteMessage = $this->core->getErrorPrefix() . "You are currently Muted for " . $reason;
                    } else {
                        $muteMessage = $this->core->getErrorPrefix() . "You are currently Muted";
                    }
                } else {
                    $expiry = Math::expirationTimerToString($entry->getExpires(), new \DateTime());

                    if($entry->hasExpired()) {
                        $muteList->remove($entry->getName());
                        return;
                    }
                    if($reason !== null or $reason !== "") {
                        $muteMessage = $this->core->getErrorPrefix() . "You are currently Muted for " . $reason . " until " . $expiry;
                    } else {
                        $muteMessage = $this->core->getErrorPrefix() . "You are currently Muted until " . $expiry;
                    }
                }
                $event->setCancelled(true);
                $player->sendMessage($muteMessage);
            }
            if($ipMuteList->isBanned($player->getAddress())) {
                $entries = $ipMuteList->getEntries();
                $entry = $entries[strtolower($player->getAddress())];
                $reason = $entry->getReason();

                if($entry->getExpires() === null) {
                    if($reason != null or $reason != "") {
                        $muteMessage = $this->core->getErrorPrefix() . "You are currently Ip Muted for " . $reason;
                    } else {
                        $muteMessage = $this->core->getErrorPrefix() . "You are currently Ip Muted";
                    }
                } else {
                    $expiry = Math::expirationTimerToString($entry->getExpires(), new \DateTime());

                    if($entry->hasExpired()) {
                        $ipMuteList->remove($entry->getName());
                        return;
                    }
                    if($reason !== null or $reason !== "") {
                        $muteMessage = $this->core->getErrorPrefix() . "You are currently Ip Muted for " . $reason . " until " . $expiry;
                    } else {
                        $muteMessage = $this->core->getErrorPrefix() . "You are currently Ip Muted until " . $expiry;
                    }
                }
                $event->setCancelled(true);
                $player->sendMessage($muteMessage);
            }
            $area = $player->getArea();

            if($area->getName() !== "") {
                if(!$player->hasPermission("core.world.area.playerchat")) {
                    if(!$area->sendChat()) {
                        $player->sendMessage($this->core->getErrorPrefix() . "You cannot Chat in the Area: " . $area->getName());
                        $event->setCancelled();
                    }
                }
            }
            $muted = $this->core->getWorld()->muted;

            if(!empty($muted)) {
                $difference = array_diff($this->core->getServer()->getOnlinePlayers(), $muted);

                if(!in_array($player, $difference)) {
                    $difference[] = $player;
                }
                $event->setRecipients($difference);
            }
            if(!$player->canChat()) {
                $event->setCancelled(true);
                $player->sendMessage($this->core->getErrorPrefix() . "You are currently in Chat cool down. Upgrade your Rank to reduce this cool down!");
            }
            $player->setChatTime();
        }
    }

    public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event) {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
            $blockList = $this->core->getEssentials()->getNameBlocks();
            $ipBlockList = $this->core->getEssentials()->getIpBlocks();
            $str = str_split($event->getMessage());

            if($str[0] !== "/") {
                return;
            }
            if($blockList->isBanned($player->getName())) {
                $entries = $blockList->getEntries();
                $entry = $entries[strtolower($player->getName())];
                $reason = $entry->getReason();

                if($entry->getExpires() === null) {
                    if($reason !== null or $reason !== "") {
                        $blockMessage = $this->core->getErrorPrefix() . "You're currently Blocked for " . $reason;
                    } else {
                        $blockMessage = $this->core->getErrorPrefix() . "You're currently Blocked";
                    }
                } else {
                    $expiry = Math::expirationTimerToString($entry->getExpires(), new \DateTime());

                    if($entry->hasExpired()) {
                        $blockList->remove($entry->getName());
                        return;
                    }
                    if($reason !== null or $reason !== "") {
                        $blockMessage = $this->core->getErrorPrefix() . "You're currently Blocked for " . $reason . " until " . $expiry;
                    } else {
                        $blockMessage = $this->core->getErrorPrefix() . "You're currently Blocked until " . $expiry;
                    }
                }
                $event->setCancelled(true);
                $player->sendMessage($blockMessage);
            }
            if($ipBlockList->isBanned($player->getAddress())) {
                $entries = $ipBlockList->getEntries();
                $entry = $entries[strtolower($player->getAddress())];
                $reason = $entry->getReason();

                if($entry->getExpires() == null) {
                    if($reason !== null or $reason !== "") {
                        $blockMessage = $this->core->getErrorPrefix() . "You're currently Ip Blocked for " . $reason;
                    } else {
                        $blockMessage = $this->core->getErrorPrefix() . "You're currently Ip Blocked";
                    }
                } else {
                    $expiry = Math::expirationTimerToString($entry->getExpires(), new \DateTime());

                    if($entry->hasExpired()) {
                        $ipBlockList->remove($entry->getName());
                        return;
                    }
                    if($reason !== null or $reason !== "") {
                        $blockMessage = $this->core->getErrorPrefix() . "You're currently Ip Blocked for " . $reason . " until " . $expiry;
                    } else {
                        $blockMessage = $this->core->getErrorPrefix() . "You're currently Ip Blocked until " . $expiry;
                    }
                }
                $event->setCancelled(true);
                $player->sendMessage($blockMessage);

                $area = $player->getArea();

                if($area->getName() !== "") {
                    if(!$player->hasPermission("core.world.area.playercommandpreprocess")) {
                        $command = explode(" ", $event->getMessage())[0];

                        if(substr($command, 0, 1) === "/") {
                            if(in_array($command, $area->getBlockedCommands())) {
                                $player->sendMessage($this->core->getErrorPrefix() . "You cannot use " . $command . " in the Area: " . $area->getName());
                                $event->setCancelled();
                            }
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

            if($area->getName() !== "") {
                if(!$player->hasPermission("core.world.area.playerdropitem")) {
                    if(!$area->itemDrop()) {
                        $player->sendMessage($this->core->getErrorPrefix() . "You cannot Drop Items in the Area: " . $area->getName());
                        $event->setCancelled();
                    }
                }
            }
        }
    }

    public function onPlayerDeath(PlayerDeathEvent $event) {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
            $replaces = [
                "{PLAYER}" => $player->getName()
            ];
            $message = "";
            $cause = $player->getLastDamageCause();

            switch($cause) {
                case $cause::CAUSE_CONTACT:
                    $stringCause = "Contact";

                    if($cause instanceof EntityDamageByBlockEvent) {
                        $replaces["{BLOCK}"] = $cause->getDamager()->getName();
                        break;
                    }
                    $replaces["{BLOCK}"] = "Unknown";
                break;
                case $cause::CAUSE_ENTITY_ATTACK:
                    $stringCause = "Kill";
                    $killer = $cause->getEntity();

                    if($killer instanceof Living) {
                        $array["{KILLER}"] = $killer->getName();
                        break;
                    }
                    $array["{KILLER}"] = "Unknown";
                break;
                case $cause::CAUSE_PROJECTILE:
                    $stringCause = "projectile";
                    $killer = $cause->getEntity();

                    if($killer instanceof Living) {
                        $array["{KILLER}"] = $killer->getName();
                        break;
                    }
                    $array["{KILLER}"] = "Unknown";
                break;
                case $cause::CAUSE_SUFFOCATION:
                    $stringCause = "Suffocation";
                break;
                case $cause::CAUSE_STARVATION:
                    $stringCause = "Starvation";
                break;
                case $cause::CAUSE_FALL:
                    $stringCause = "Fall";
                break;
                case $cause::CAUSE_FIRE:
                    $stringCause = "Fire";
                break;
                case $cause::CAUSE_FIRE_TICK:
                    $stringCause = "On-Fire";
                break;
                case $cause::CAUSE_LAVA:
                    $stringCause = "Lava";
                break;
                case $cause::CAUSE_DROWNING:
                    $stringCause = "Drowning";
                break;
                case $cause::CAUSE_ENTITY_EXPLOSION:
                case $cause::CAUSE_BLOCK_EXPLOSION:
                    $stringCause = "Explosion";
                break;
                case $cause::CAUSE_VOID:
                    $stringCause = "Void";
                break;
                case $cause::CAUSE_SUICIDE:
                    $stringCause = "Suicide";
                break;
                case $cause::CAUSE_MAGIC:
                    $stringCause = "Magic";
                break;
                default:
                    $stringCause = "Normal";
                break;
            }
            if(!empty($this->core->getBroadcast()->getDeaths($stringCause))) {
                $message = $this->core->getBroadcast()->getJoins($stringCause);

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

    public function onPlayerExhaust(PlayerExhaustEvent $event) {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
            $area = $player->getArea();

            if($area->getName() !== "") {
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
            $interacts = $player->addToInteract();

            if($interacts["amount"] >= $this->core->getAntiCheat()->getAutoClickAmount()) {
                $this->core->getServer()->getLogger()->warning($this->core->getErrorPrefix() . $player->getName() . " seems to have an Auto Clicker");
                $player->kick($this->core->getErrorPrefix() . "An Auto Clicker was detected");
            }
            $area = $player->getArea();

            if($area->getName() !== "") {
                if(!$player->hasPermission("core.world.area.playerinteract")) {
                    if(!$area->usable()) {
                        if(in_array($event->getBlock()->getId(), Entity::USABLES)) {
                            $player->sendMessage($this->core->getErrorPrefix() . "You cannot Interact with " . $event->getBlock()->getName() . " in the Area: " . $area->getName());
                            $event->setCancelled();
                        }
                    }
                    if(!$area->consume()) {
                        if(in_array($event->getBlock()->getId(), Entity::CONSUMABLES)) {
                            $player->sendMessage($this->core->getErrorPrefix() . "You cannot Use " . $event->getItem()->getName() . " in the Area: " . $area->getName());
                            $event->setCancelled();
                        }
                    }
                    if(!$area->editable()) {
                        if(in_array($event->getBlock()->getId(), Entity::OTHER)) {
                            $player->sendMessage($this->core->getErrorPrefix() . "You cannot Edit the Area: " . $area->getName());
                            $event->setCancelled();
                        }
                    }
                }
            }
        }
    }

    public function onPlayerItemHeld(PlayerItemHeldEvent $event){
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {

            if($player->isFishing()) {
                if($event->getSlot() !== $player->lastHeldSlot) {
                    $player->setFishing(false);
                }
            }
            $player->lastHeldSlot = $event->getSlot();
        }
    }

    public function onPlayerJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();

        if ($player instanceof CorePlayer) {
            $player->setCore($this->core);

            if (in_array($player->getLevel(), Messages::WORLDS)) {
                if ($this->core->getBroadcast()->getBossBar()->entityRuntimeId === null) {
                    $this->core->getBroadcast()->getBossBar()->entityRuntimeId = $this->core->getBroadcast()->getBossBar()->add([$player], str_replace("{PREFIX}", $this->core->getPrefix(), Messages::NOT_REGISTERED_MESSAGE));
                } else {
                    $player->sendBossBar($this->core->getBroadcast()->getBossBar()->entityRuntimeId, $player->getBossBarText());
                }
            }
            $message = "";

            if (!$player->hasPlayedBefore()) {
                if (!empty(Broadcasts::JOINS["First"])) {
                    $message = str_replace([
                        "{PLAYER}",
                        "{TIME}",
                        "{NAME_TAG_FORMAT}"
                    ], [
                        $player->getName(),
                        date($this->core->getBroadcast()->getFormats("date_time")),
                        str_replace("{DISPLAY_NAME}", $player->getName(), $player->getCoreUser()->getRank()->getNameTagFormat())
                    ], $this->core->getBroadcast()->getJoins("first"));
                }
            }
            if ($player->hasPermission("core.stats.join")) {
                if (!empty($this->core->getBroadcast()->getJoins("normal"))) {
                    $message = str_replace([
                        "{PLAYER}",
                        "{TIME}",
                        "{NAME_TAG_FORMAT}"
                    ], [
                        $player->getName(),
                        date($this->core->getBroadcast()->getFormats("date_ime")),

                        str_replace("{DISPLAY_NAME}", $player->getName(), $player->getCoreUser()->getRank()->getNameTagFormat())
                    ], $this->core->getBroadcast()->getJoins("normal"));
                }
            }
            $event->setJoinMessage($message);
            $player->join();
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
            $player->rotateNPCs();

            if(!$event->getFrom()->equals($event->getTo())) {
                if($player->updateArea()) {
                    $player->setMotion($event->getFrom()->subtract($player->getLocation()->normalize()->multiply(4)));
                }
            }
            if($player->getArea()->getName() === "Lobby" && $event->getTo()->getFloorY() < 0) {
                $player->teleport($player->getLevel()->getSafeSpawn());
            }
        }
    }

    public function onPlayerPreLogin(PlayerPreLoginEvent $event) {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
            if($this->core->getStats()->getCoreUserXuid($player->getXuid())->getName() !== $player->getName()) {
                $player->getCoreUser()->setName($player->getName());
            }
            $banList = $this->core->getEssentials()->getNameBans();
            $ipBanList = $this->core->getEssentials()->getIpBans();

            if($banList->isBanned($player)) {
                $entries = $banList->getEntries();
                $entry = $entries[strtolower($player->getName())];

                if($entry->getExpires() === null) {
                    $reason = $entry->getReason();

                    if($reason !== null or $reason !== "") {
                        $banMessage = $this->core->getErrorPrefix() . "You are currently Banned for " . $reason;
                    } else {
                        $banMessage = $this->core->getErrorPrefix() . "You are currently Banned";
                    }
                } else {
                    $expiry = Math::expirationTimerToString($entry->getExpires(), new \DateTime());

                    if($entry->hasExpired()) {
                        $banList->remove($entry->getName());
                        return;
                    }
                    $banReason = $entry->getReason();

                    if($banReason !== null || $banReason !== "") {
                        $banMessage = $this->core->getErrorPrefix() . "You are currently Banned for " . $banReason . " until " . $expiry;
                    } else {
                        $banMessage = $this->core->getErrorPrefix() . "You are currently Banned until " . $expiry;
                    }
                }
                $event->setCancelled(true);
                $player->sendMessage($banMessage);
            }
            if($ipBanList->isBanned($player)) {
                $entries = $ipBanList->getEntries();
                $entry = $entries[strtolower($player->getName())];

                if($entry->getExpires() === null) {
                    $reason = $entry->getReason();

                    if($reason !== null or $reason !== "") {
                        $banMessage = $this->core->getErrorPrefix() . "You are currently Ip Banned for " . $reason;
                    } else {
                        $banMessage = $this->core->getErrorPrefix() . "You are currently Ip Banned";
                    }
                } else {
                    $expiry = Math::expirationTimerToString($entry->getExpires(), new \DateTime());

                    if($entry->hasExpired()) {
                        $ipBanList->remove($entry->getName());
                        return;
                    }
                    $banReason = $entry->getReason();

                    if($banReason !== null || $banReason !== "") {
                        $banMessage = $this->core->getErrorPrefix() . "You are currently Ip Banned for " . $banReason . " until " . $expiry;
                    } else {
                        $banMessage = $this->core->getErrorPrefix() . "You are currently Ip Banned until " . $expiry;
                    }
                }
                $event->setCancelled(true);
                $player->sendMessage($banMessage);
            }
            if(count($this->core->getServer()->getOnlinePlayers()) - 1 < $this->core->getServer()->getMaxPlayers()) {
                $server = $this->core->getNetwork()->getServerFromIp($this->core->getServer()->getIp());

                if(!$server->isWhitelisted() && !$player->hasPermission("core.network." . $server->getName() . ".whitelist")) {
                    if(!empty($this->core->getBroadcast()->getKicks("whitelisted"))) {
                        $message = str_replace([
                            "{PLAYER}",
                            "{TIME}",
                            "{ONLINE_PLAYERS}",
                            "{MAX_PLAYERS}",
                            "{PREFIX}"
                        ], [
                            $player->getName(),
                            date($this->core->getBroadcast()->getFormats("date_time")),
                            count($this->core->getServer()->getOnlinePlayers()),
                            $this->core->getServer()->getMaxPlayers(),
                            $this->core->getPrefix()
                        ], $this->core->getBroadcast()->getKicks("whitelisted"));

                        $player->close($message);
                        $event->setCancelled();
                    }
                }
            } else {
                if(!empty($this->core->getBroadcast()->getKicks("full"))) {
                    $message = str_replace([
                        "{PLAYER}",
                        "{TIME}",
                        "{ONLINE_PLAYERS}",
                        "{MAX_PLAYERS}",
                        "{PREFIX}"
                    ], [
                        $player->getName(),
                        date($this->core->getBroadcast()->getFormats("date_time")),
                        count($this->core->getServer()->getOnlinePlayers()),
                        $this->core->getServer()->getMaxPlayers(),
                        $this->core->getPrefix()
                    ], $this->core->getBroadcast()->getKicks("full"));

                    $player->close($message);
                    $event->setCancelled();
                }
            }
        }
    }

    public function onPlayerQuit(PlayerQuitEvent $event) {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
            $message = "";

            if($player->hasPermission("core.stats.quit")) {
                if(!empty($this->core->getBroadcast()->getQuits("normal"))) {
                    $message = str_replace([
                        "{PLAYER}",
                        "{TIME}",
                        "{NAME_TAG_FORMAT}"
                    ], [
                        $player->getName(),
                        date($this->core->getBroadcast()->getFormats("date_time")),
                        str_replace("{DISPLAY_NAME}", $player->getName(), $player->getCoreUser()->getRank()->getNameTagFormat())
                    ], $this->core->getBroadcast()->getQuits("normal"));
                }
            }
            $event->setQuitMessage($message);
            $player->leave();
        }
    }

    public function onEntityDamage(EntityDamageEvent $event) {
        $player = $event->getEntity();

        if($player instanceof CorePlayer) {
            if($event instanceof EntityDamageByEntityEvent) {
                $area = $player->getArea();

                if($area->getName() !== "") {
                    if(!$player->hasPermission("core.world.area.entitydamage")) {
                        if(!$area->pvp()) {
                            $player->sendMessage($this->core->getErrorPrefix() . "You cannot PvP in the Area: " . $area->getName());
                            $event->setCancelled();
                        }
                    }
                }
            }
            if($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
                if($event->getEntity()->getLevel()->getBlock($event->getEntity()->subtract(0, 1, 0))->getId() == Block::SLIME_BLOCK) {
                    $event->setCancelled(true);
                }
            }
        }
    }


    public function onEntityDeath(EntityDeathEvent $event) {
        $xp = Entity::getXpDropsForEntity($event->getEntity());

        if($xp > 0) {
            $event->getEntity()->getLevel()->dropExperience($event->getEntity()->asVector3(), $xp);
        }
    }

    public function onEntityLevelChange(EntityLevelChangeEvent $event) {
        $entity = $event->getEntity();

        if($entity instanceof CorePlayer) {
            if(!in_array($event->getTarget(), $this->core->getBroadcast()->getBossBar()->getWorlds())) {
                $this->core->getBroadcast()->getBossBar()->remove([$entity], $this->core->getBroadcast()->getBossBar()->entityRuntimeId);
            }
            if($this->core->getBroadcast()->getBossBar()->entityRuntimeId === null) {
                $this->core->getBroadcast()->getBossBar()->entityRuntimeId = $this->core->getBroadcast()->getBossBar()->add([$entity], str_replace("{PREFIX}", $this->core->getPrefix(), $this->core->getBroadcast()->getBossBar()->getNotRegisteredMessage()));
            } else {
                $entity->sendBossBar($this->core->getBroadcast()->getBossBar()->entityRuntimeId, $entity->getBossBarText());
            }
            $origin = $event->getOrigin();
            $target = $event->getTarget();

            if(!empty($this->core->getBroadcast()->getDimensions("change"))) {
                $message = str_replace([
                    "{PLAYER}",
                    "{TIME}",
                    "{ORIGIN}",
                    "{TARGET}",
                    "{NAME_TAG_FORMAT}"
                ], [
                    $entity->getName(),
                    date($this->core->getBroadcast()->getFormats("date_time")),
                    $origin->getName(),
                    $target->getName(),
                    str_replace("{DISPLAY_NAME}", $entity->getName(), $entity->getCoreUser()->getRank()->getNameTagFormat())
                ], $this->core->getBroadcast()->getDimensions("change"));

                $this->core->getServer()->broadcastMessage($message);
            }
            $entity->checkNPCLevelChange($event->getTarget());
            $entity->checkFloatingTextsLevelChange($event->getTarget());
        }
    }

    public function onEntityExplode(EntityExplodeEvent $event) {
        foreach($event->getBlockList() as $block) {
            $area = $this->core->getWorld()->getAreaFromPosition($block);

            if($area->getName() !== "") {
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
            if($entity::NETWORK_ID !== 87) {
                return;
            }
            $area = $this->core->getWorld()->getAreaFromPosition($entity);

            if($area->getName() !== "") {
                if(!$player->hasPermission("core.world.area.projectilelaunch")) {
                    if(!$area->enderPearl()) {
                        $player->sendMessage($this->core->getErrorPrefix() . "You cannot Enderpearl in the Area: " . $area->getName());
                        $event->setCancelled();
                    }
                }
            }
        }
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $event) {
        $player = $event->getPlayer();
        $release = !IS_DEVELOPMENT_BUILD;
        $pk = $event->getPacket();

        if($player instanceof CorePlayer) {
            if($pk instanceof ServerSettingsRequestPacket) {
                $ev = new ServerSettingsRequestEvent($player = $release ? $player : $event->getOrigin()->getPlayer());
                $release ? $this->core->getServer()->getPluginManager()->callEvent($event) : $event->call();

                if(($form = $ev->getForm()) !== null) {
                    if($player instanceof CorePlayer) {
                        $player->sendSetting($form);
                    }
                }
            }
            if($pk instanceof LoginPacket) {
                if($pk->protocol < ProtocolInfo::CURRENT_PROTOCOL) {
                    if(!empty($this->core->getBroadcast()->getKicks("outdated")["client"])) {
                        $message = str_replace([
                            "{PLAYER}",
                            "{TIME}"
                        ], [
                            $player->getName(),
                            date($this->core->getBroadcast()->getFormats("date_time"))
                        ], $this->core->getBroadcast()->getKicks("outdated")["client"]);

                        $player->close($message);
                        $event->setCancelled(true);
                    }
                } else if($pk->protocol > ProtocolInfo::CURRENT_PROTOCOL) {
                    if(!empty($this->core->getBroadcast()->getKicks("outdated")["server"])) {
                        $message = str_replace([
                            "{PLAYER}",
                            "{TIME}"
                        ], [
                            $player->getName(),
                            date($this->core->getBroadcast()->getFormats("date_time"))
                        ], $this->core->getBroadcast()->getKicks("outdated")["server"]);

                        $player->close($message);
                        $event->setCancelled(true);
                    }
                }
            }
            if($pk instanceof InventoryTransactionPacket) {
                if($pk->transactionType === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY && $pk->trData->actionType === InventoryTransactionPacket::USE_ITEM_ON_ENTITY_ACTION_INTERACT) {
                    $entity = $pk->trData;

                    foreach($this->core->getEssence()->getNPCs() as $NPC) {
                        if($entity->entityRuntimeId === $NPC->getEID()) {
                            $NPC->onInteract($player);
                        }
                    }
                }
            }
        }
    }

    public function onBlockBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
            $area = $this->core->getWorld()->getAreaFromPosition($event->getBlock());

            if($area->getName() !== "") {
                if(!$player->hasPermission("core.world.area.blockbreak")) {
                    if(!$area->editable()) {
                        $player->sendMessage($this->core->getErrorPrefix() . "You cannot Break Blocks the Area: " . $area->getName());
                        $event->setCancelled();
                    }
                }
            }
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event) {
        $player = $event->getPlayer();

        if($player instanceof CorePlayer) {
            $area = $this->core->getWorld()->getAreaFromPosition($event->getBlock());

            if($area->getName() !== "") {
                if(!$player->hasPermission("core.world.area.blockplace")) {
                    if(!$area->editable()) {
                        $player->sendMessage($this->core->getErrorPrefix() . "You cannot Place Blocks the Area: " . $area->getName());
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
                        $viewer->sendMessage($this->core->getErrorPrefix() . "You cannot Pickup Items in the Area: " . $area->getName());
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

            if($area->getName() !== "") {
                if(!$viewer->hasPermission("core.world.area.inventorypickupitem")) {
                    if(!$area->itemPickup()) {
                        $viewer->sendMessage($this->core->getErrorPrefix() . "You cannot Pickup Items in the Area: " . $area->getName());
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

            if($area->getName() !== "") {
                if(!$source->hasPermission("core.world.area.inventorytransaction")) {
                    if(!$area->inventoryTransaction()) {
                        foreach($actions as $action) {
                            if($action instanceof SlotChangeAction) {
                                $inventory = $action->getInventory();

                                if($inventory instanceof PlayerInventory or $inventory instanceof PlayerCursorInventory) {
                                    $source->sendMessage($this->core->getErrorPrefix() . "You cannot do Transactions in your Inventory in the Area: " . $area->getName());
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
        $level = $event->getLevel();
        $packCenter = new Vector3(mt_rand($chunk->getX() << 4, (($chunk->getX() << 4) + 15)), mt_rand(0, $level->getWorldHeight() - 1), mt_rand($chunk->getZ() << 4, (($chunk->getZ() << 4) + 15)));
        $lightLevel = $level->getFullLightAt($packCenter->x, $packCenter->y, $packCenter->z);

        if(!$level->getBlockAt($packCenter->x, $packCenter->y, $packCenter->z)->isSolid() and $lightLevel > 8) {
            $biomeId = $level->getBiomeId($packCenter->x, $packCenter->z);
            $entityList = $this->core->getMCPE()::BIOME_HOSTILE_MOBS[$biomeId];
			
			if(empty($entityList)) {
				return;
			}
			$entityId = $entityList[array_rand($this->core->getMCPE()::BIOME_HOSTILE_MOBS[$biomeId])];

            if(!$level->getBlockAt($packCenter->x, $packCenter->y, $packCenter->z)->isSolid()) {
                for($attempts = 0, $currentPackSize = 0; $attempts <= 12 and $currentPackSize < 4; $attempts++) {
                    $x = mt_rand(-20, 20) + $packCenter->x;
                    $z = mt_rand(-20, 20) + $packCenter->z;

                    foreach($this->core->getMCPE()->registeredEntities as $class => $param) {
                        if($class instanceof AnimalBase and $class::NETWORK_ID === $entityId) {
                            $entity = $class::spawnMob(new Position($x + 0.5, $packCenter->y, $z + 0.5, $level));

                            if($entity !== null) {
                                $currentPackSize++;
                            }
                        }
                    }
                }
            }
        } else if(!$level->getBlockAt($packCenter->x, $packCenter->y, $packCenter->z)->isSolid() and $lightLevel <= 7) {
            $biomeId = $level->getBiomeId($packCenter->x, $packCenter->z);
            $entityList = $this->core->getMCPE()::BIOME_HOSTILE_MOBS[$biomeId];
			
			if(empty($entityList)) {
				return;
			}
			$entityId = $entityList[array_rand($this->core->getMCPE()::BIOME_HOSTILE_MOBS[$biomeId])];
			
            if(!$level->getBlockAt($packCenter->x, $packCenter->y, $packCenter->z)->isSolid()) {
                for($attempts = 0, $currentPackSize = 0; $attempts <= 12 and $currentPackSize < 4; $attempts++) {
                    $x = mt_rand(-20, 20) + $packCenter->x;
                    $z = mt_rand(-20, 20) + $packCenter->z;

                    foreach($this->core->getMCPE()->registeredEntities as $class => $param) {
                        if($class instanceof MonsterBase and $class::NETWORK_ID === $entityId) {
                            $entity = $class::spawnMob(new Position($x + 0.5, $packCenter->y, $z + 0.5, $level));
                            
							if($entity !== null) {
                                $currentPackSize++;
                            }
                        }
                    }
                }
            }
        }
    }

    public function onChunkUnload(ChunkUnloadEvent $event) {
        $chunk = $event->getChunk();

        foreach($chunk->getEntities() as $entity) {
            if($entity instanceof CreatureBase and !$entity->isPersistent()) {
                $entity->flagForDespawn();
            }
        }
    }

    public function onQueryRegenerate(QueryRegenerateEvent $event) {
        $event->setPlayerCount(count($this->core->getNetwork()->getTotalOnlinePlayers()));
        $event->setMaxPlayerCount($this->core->getNetwork()->getTotalMaxSlots());

        $players = [];

        foreach($this->core->getNetwork()->getTotalOnlinePlayers() as $onlinePlayer) {
            $players[] = $onlinePlayer;
        }
        foreach($this->core->getNetwork()->getServers() as $server) {
            $server->query();
        }
        $event->setPlayerList($players);
    }
}