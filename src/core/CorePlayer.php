<?php

declare(strict_types = 1);

namespace core;

use core\utils\Item;

use core\broadcast\Broadcasts;
use core\broadcast\bossbar\Messages;

use core\essence\npc\NPC;
use core\essence\floatingtext\FloatingText;

use core\mcpe\network\PlayerNetworkSessionAdapter;
use core\mcpe\entity\{
	Linkable,
	Lookable,
	Interactable
};
use core\mcpe\entity\projectile\FishingHook;
use core\mcpe\entity\monster\walking\Enderman;
use core\mcpe\block\Pumpkin;

use core\network\server\Server;

use core\stats\task\PlayerJoin;
use core\stats\Statistics;

use core\vote\VoteData;

use core\world\area\Area;

use form\{
	CustomFormResponse,
	Form,
	MenuForm,
	CustomForm,
	ServerSettingsForm
};
use form\element\{
	Button,
	Dropdown,
	Image,
	Input,
	Label
};

use pocketmine\Player;

use pocketmine\network\SourceInterface;

use pocketmine\network\mcpe\protocol\{
	ActorEventPacket,
	SetPlayerGameTypePacket,
	InteractPacket,
	ActorPickRequestPacket,
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

class CorePlayer extends Player {
    /** @var \core\Core*/
    private $core;
	private $coreUser;

	const STAFF = "staff";
	const NORMAL = "normal";
	const VIP = "vip";

	const SCOREBOARD = 0;
	const POPUP = 1;

    private $interacts = [];

    private $chatTime = 0;

	public $lastHeldSlot = 0;

    private $fishing = false;

    public $chatType = self::NORMAL;

    public $usingElytra = false, $allowCheats = false, $fly = false;

    public $hud = [];
    /** 
	 * @var null|FishingHook 
	 */
    public $fishingHook = null;
	/** 
	 * @var int|null
	 */
	protected $lastMovement = null;
	/** 
	 * @var PermissionAttachment 
	 */
	public $attachment;

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
		if(!$this->isInitialized()) {
			throw new \RuntimeException("Tried to get core user of uninitialized player");
		}
        return $this->coreUser;
    }

    public function join(CoreUser $coreUser) {
		if(!$this->isInitialized()) {
			$this->coreUser = $coreUser;
		}
		if($this->isOnline()) {
			$this->attachment = $this->addAttachment($this->core);
		
			$this->updatePermissions();
			$this->spawnNPCs();
			$this->spawnFloatingTexts();
			$this->sendBossBar();

			$this->core->getWorld()->players[$this->getName()] = null;

			$this->updateArea();

			foreach($this->core->getAntiCheat()->getCheats() as $cheat) {
				$cheat->set($this);
			}
			$this->core->getScheduler()->scheduleDelayedTask(new PlayerJoin($this->core, $this), 20);
			
			if($this->getCoreUser()->getName() !== $this->getName()) {
				$this->getCoreUser()->setName($this->getName());
			}
			$this->getCoreUser()->setServer($this->core->getNetwork()->getServerFromIp($this->getServer()->getIp()));
			$this->getCoreUser()->save();
			$this->setHud(self::SCOREBOARD, true);
			$this->setHud(self::POPUP, true);
			$this->setNameTag($this->getCoreUser()->getRank()->getNameTagFormat());
		}
    }

    public function leave() {
		$this->despawnNPCs();
		
		if(!is_null($this->getAttachment())) {
			$this->removeAttachment($this->getAttachment());
		}
        $this->removeBossBar();
		
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
        $format = str_replace("{PREFIX}", $this->core->getPrefix(), $format);
        $format = str_replace("{TIME}", date(Broadcasts::FORMATS["date_time"]), $format);
        $format = str_replace("{MESSAGE}", $broadcast, $format);
        $format = str_replace("{SENDER}", $this->getName(), $format);
        return $format;
    }

    public function sendBossBar() {
		$this->core->getBroadcast()->getBossBar()->get()->addPlayer($this);
		$this->setText();
    }

    public function removeBossBar() {
		$this->core->getBroadcast()->getBossBar()->get()->removePlayer($this);
	}

	public function setText() {
    	$bossBar = $this->core->getBroadcast()->getBossBar()->get();

		if(!empty(Messages::HEAD_MESSAGE)) {
			$bossBar->setTitle($this->formatBossBar(Messages::HEAD_MESSAGE) . TextFormat::RESET);
		}
		$currentMSG = Messages::CHANGING["messages"][$this->core->getBroadcast()->getBossBar()->int % count(Messages::CHANGING["messages"])];

		if(strpos($currentMSG, '%') > -1) {
			$percentage = substr($currentMSG, 1, strpos($currentMSG, '%') - 1);

			if(is_numeric($percentage)) {
				$bossBar->setPercentage($percentage / 100);
			}
			$bossBar->setSubTitle(substr($currentMSG, strpos($currentMSG, '%') + 2));
		}
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
			count($this->getServer()->getOnlinePlayers()),
			date(Broadcasts::FORMATS["date_time"])
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

    public function updateFloatingText(FloatingText $floatingText) {
    	$floatingText->spawnTo($this);
	}

    public function areNPCSSpawned() : bool {
        foreach($this->core->getEssence()->getNPCs() as $NPC) {
            return $NPC->isSpawnedTo($this);
        }
        return false;
    }

    public function checkNPCLevelChange() {
        foreach($this->core->getEssence()->getNPCs() as $NPC) {
            if($NPC instanceof NPC) {
                if($NPC->getPosition()->getLevel()->getName() === $this->getLevel()->getName()) {
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

    public function getChatType() : string {
    	return $this->chatType;
	}

	public function setChatType(string $chatType) {
    	$this->chatType = $chatType;
	}

	public function getInteracts() : array {
		return $this->interacts;
	}

    public function addToInteract() {
        if(($this->interacts["time"] ?? 0) === time()) {
            $this->interacts["amount"]++;
            return;
        }
        $this->interacts["time"] = time();
        $this->interacts["amount"] = 1;
        return;
    }

    public function isFishing() : bool {
        return $this->fishing;
    }

    public function setFishing(bool $fishing) {
        $this->fishing = $fishing;

        if(!$fishing) {
			if($this->fishingHook instanceof FishingHook) {
				$this->fishingHook->broadcastEntityEvent(ActorEventPacket::FISH_HOOK_TEASE, null, $this->fishingHook->getViewers());

				if(!$this->fishingHook->isFlaggedForDespawn()) {
					$this->fishingHook->flagForDespawn();
				}
				$this->fishingHook = null;
			}
		}
    }
	
	public function flying() : bool {
		return $this->fly;
	}
	
	public function setFly(bool $fly = true) {
		$this->fly = $fly;

		$this->setAllowFlight($fly);
		
		if(!$fly) {
			$this->setFlying(false);
		}
	}

	public function hasHud(int $type) {
    	return isset($this->hud[$type]);
	}

	public function setHud(int $type, bool $hud = true) {
    	$this->hud[$type] = $hud;

		if($hud) {
			$this->getCoreUser()->getServer()->addHud($type, $this);
		} else {
			$this->getCoreUser()->getServer()->removeHud($type, $this);
		}
	}

    public function getAttachment() : ?PermissionAttachment {
        return $this->attachment;
    }

    public function updatePermissions() {
        $permissions = [];
		
		if(!$this->isInitialized()) {
			return;
		}
		$attachment = $this->getAttachment();

        $attachment->clearPermissions();
		
        foreach($this->getCoreUser()->getAllPermissions() as $permission) {
            if($permission === "*") {
                foreach($this->getServer()->getPluginManager()->getPermissions() as $temp) {
                    $permissions[$temp->getName()] = true;
                }
            } else if(is_string($permission)) {
				$attachment->setPermission($permission, true);
			}
        }
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
				$b = new Button($name, new Image($server->getIcon(), Image::TYPE_URL));

				$b->setId($server->getName());
				
                if(empty($server->getIcon())) {
                    $b = new Button($name);

                    $b->setId($server->getName());
				}	
				$options[] = $b;
            }
        }
        $this->sendForm(new MenuForm(TextFormat::GOLD . "Server", TextFormat::LIGHT_PURPLE . "Pick a Server", $options,
			function(Player $player, Button $button) : void {
				if($player instanceof CorePlayer) {
					$server = Core::getInstance()->getNetwork()->getServer($button->getId());

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
			},
			function(Player $player) : void {
				$player->sendMessage(Core::getInstance()->getPrefix() . "Closed Servers Form");
        }));
    }

    public function sendProfileForm(string $key = "profile", CoreUser $user = null) {
        switch($key) {
            case "profile":
                $this->sendMessage($this->core->getPrefix() . "Opened Profile menu");

                $b1 = new Button(TextFormat::GRAY . "Global");

                $b1->setId(1);

                $b2 = new Button(TextFormat::GRAY . "Lobby", new Image($this->core->getNetwork()->getServer("Lobby")->getIcon(), Image::TYPE_URL));

                $b2->setId(2);

                $b3 = new Button(TextFormat::GRAY . "Factions", new Image($this->core->getNetwork()->getServer("Factions")->getIcon()));

                $b3->setId(3);

                $options = [
                	$b1,
					$b2,
					$b3
				];
				$per = $user;
                $profile = $per = null ? $user->getName() . "'s Profile" : "Your Profile";

                $this->sendForm(new MenuForm(TextFormat::GOLD . $profile, TextFormat::GRAY . "Select an Option", $options,
					function(Player $player, Button $button) use ($user) : void {
						if($player instanceof CorePlayer) {
							switch($button->getId()) {
								case 1:
									$player->sendProfileForm("global", $user);
								break;
								case 2:
									//$player->sendProfileForm("lobby", $this->user);
								break;
								case 3:
									//$player->sendProfileForm("factions", $this->user);
								break;
							}
						}
					},
					function(Player $player) : void {
						$player->sendMessage(Core::getInstance()->getPrefix() . "Closed Profile menu");
					}
				));
            break;
            case "global":           
				$profile = "Your Profile";				
				$server = $this->getCoreUser()->getServer()->getName();
				$rank = $this->getCoreUser()->getRank()->getFormat();
				$coins = $this->getCoreUser()->getCoins();
				$balance = $this->getCoreUser()->getBalance();
				
				if(!is_null($user)) {
					if(!is_null($user->getServer())) {
						$server = $user->getServer()->getName();
					} else {
						$server = "Offline";
					}
					$rank = $user->getRank()->getFormat();
					$coins = $user->getCoins();
					$balance = $user->getBalance();
					$profile = $user->getName() . "'s Profile";
				} 
                $l1 = new Label(TextFormat::GRAY . "Rank: " . $rank);
                $l2 = new Label(TextFormat::GRAY . "Coins: " . Statistics::UNITS["coins"] . $coins);
                $l3 = new Label(TextFormat::GRAY . "Balance: " . Statistics::UNITS["balance"] . $balance);
                $l4 = new Label(TextFormat::GRAY . "Server: " . $server);

                $data = [
                	$l1,
					$l2,
					$l3,
					$l4
                ];
				
                $this->sendForm(new CustomForm(TextFormat::GOLD . $profile . TextFormat::BLUE . " (Global)", $data, function(Player $player) : void {},
					function(Player $player) : void {
						$player->sendMessage(Core::getInstance()->getPrefix() . "Closed Profile menu");
					}
				));
            break;
        }
    }

    public function sendCurrencyChangeForm() {
		$e1 = new Label(TextFormat::GRAY . "Your Coins: " . Statistics::UNITS["coins"] . $this->getCoreUser()->getCoins());

		$e1->setValue(1);

		$e2 = new Label(TextFormat::GRAY . "Your Balance: " . Statistics::UNITS["balance"] . $this->getCoreUser()->getBalance());

		$e2->setValue(2);

		$e3 = new Label(TextFormat::GRAY . "Value of a Coin (Transferred): " . Statistics::COIN_VALUE);

		$e3->setValue(3);

		$e4 = new Dropdown(TextFormat::GRAY . "Currency Type To Change Too", ["Coins", "Balance"]);

		$e4->setValue(4);

		$e5 = new Input(TextFormat::GRAY . "Amount to Exchange", "100");

		$e5->setValue(5);

		$elements = [
			$e1,
			$e2,
			$e3,
			$e4,
			$e5
		];

		$this->sendForm(new CustomForm(TextFormat::GOLD . "Currency Exchange", $elements,
			function(Player $player, CustomFormResponse $data) : void {
				if($player instanceof CorePlayer) {
					$type = $data->getDropdown()->getSelectedOption();
					$amount = $data->getInput()->getValue();

					if(!is_int((int) $amount)) {
						$player->sendMessage(Core::getInstance()->getErrorPrefix() . "Not a valid Type or valid Amount inputted");
						return;
					}
					$user = $player->getCoreUser();

					if($type === "Coins") {
						if($amount < Statistics::COIN_VALUE) {
							$player->sendMessage(Core::getInstance()->getErrorPrefix() . "Amount must be greater than 1000 to switch to Coins");
							return;
						}
						if($user->getBalance() < $amount) {
							$player->sendMessage(Core::getInstance()->getErrorPrefix() . "You do not have enough Balance");
							return;
						}
						$user->setCoins($amount / Statistics::COIN_VALUE);
						$user->setBalance($user->getBalance() - $amount);
						$player->sendMessage("Transferred " . $amount . " Balance to Coins");
					}
					if($type === "Balance") {
						if($user->getCoins() < $amount) {
							$player->sendMessage(Core::getInstance()->getErrorPrefix() . "You do not have enough Balance");
							return;
						}
						$user->setBalance($user->getBalance() * Statistics::COIN_VALUE);
						$user->setCoins($user->getCoins() - $amount);
						$player->sendMessage("Transferred " . $amount . " Coins to Balance");
					}
				}
			},
			function(Player $player) : void {
				$player->sendMessage(Core::getInstance()->getPrefix() . "Closed Currency Change menu");
			}
		));
	}

	public function getServerSettingsForm(string $server = "lobby") : ServerSettingsForm {
		$elements = [
			new Label(TextFormat::GRAY . "Coming Soon!")
		];
		$image = new Image("http://icons.iconarchive.com/icons/double-j-design/diagram-free/128/settings-icon.png");
		$form = new ServerSettingsForm($this->core->getPrefix() . "Athena Settings", $elements, $image,
			function(Player $player, CustomFormResponse $response) {

			}
		);

    	switch($server) {
			case "lobby":
				return $form;
			break;
			case "factions":
				$elements = [
					new Label(TextFormat::GRAY . "Coming Soon!")
				];
				$image = new Image("http://icons.iconarchive.com/icons/double-j-design/diagram-free/128/settings-icon.png");
				$form = new ServerSettingsForm($this->core->getPrefix() . "Athena Factions Settings", $elements, $image,
					function(Player $player, CustomFormResponse $response) {

					}
				);
				return $form;
			break;
		}
		return $form;
	}

    public function claimVote() {
    	$item = Item::getRandomItems(VoteData::ITEMS);

    	if($this->getInventory()->canAddItem($item)) {
    		$this->getInventory()->addItem($item);
		} else {
    		$this->getLevel()->dropItem($this, $item);
		}
        foreach(VoteData::COMMANDS as $key => $command) {
            $command = str_replace("{PLAYER}", $this->getName(), $command);

            $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $command);
        }
		$this->sendMessage($this->core->getPrefix() . "Thanks for Voting!");
		$this->core->getServer()->broadcastMessage($this->core->getPrefix() . $this->getName() . " Voted for the Server and got rewarded!");
    }

    public function getArea() : ?Area {
		$area = $this->core->getWorld()->players[$this->getName()] ?? null;

        return $area !== null ? $this->core->getWorld()->getArea($area->getName()) : null;
    }

    public function updateArea() : bool {
        $oldArea = $this->getArea();
		$newArea = $this->core->getWorld()->getAreaFromPosition($this->getPosition());
		
		if(!is_null($newArea)) {
			if($newArea !== $oldArea) {
				$this->core->getWorld()->players[$this->getName()] = $newArea;

				return $this->areaChange($oldArea, $newArea);
			}
		}
        return false;
    }

    public function areaChange(?Area $oldArea, Area $newArea) : bool {
        if(!is_null($oldArea)) {
            if(!$oldArea->allowedLeave() && $this->hasPermission("core.world." . $oldArea->getName() . ".leave")) {
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
            if($oldArea->getFly() === Area::FLY_SUPERVISED && $this->hasPermission("core.world." . $newArea->getName() . ".fly")) {
                $this->setAllowFlight(false);

                $pk = new SetPlayerGameTypePacket();
                $pk->gamemode = $this->gamemode & 0x01;

                $this->sendDataPacket($pk);
                $this->setFlying(false);
                $this->sendSettings();
            }
            if(!$this->hasPermission("core.world." . $oldArea->getName() . ".whitelist")) {
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
        if(!is_null($newArea)) {
			if(!$newArea->allowedEnter() && $this->hasPermission("core.world." . $newArea->getName() . ".enter")) {
				$this->sendMessage($this->core->getErrorPrefix() . "You cannot enter this area");
                return false;
			}
            if($message = $newArea->getEnterNotifications() !== "") {
                $this->sendMessage($message);
            }
            if(!$newArea->receiveChat()) {
                $this->core->getWorld()->muted[$this->getName()] = $this;
            }
            $effects = $newArea->getAreaEffects();

            if(!empty($effects) && $this->hasPermission("core.world." . $newArea->getName() . ".effects")) {
                $this->removeAllEffects();

                foreach($effects as $effect) {
                    $this->addEffect($effect);
                }
            }
            if(!$this->hasPermission("core.world." . $newArea->getName() . ".whitelist")) {
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

		$id = $idProperty->getValue($this);

		$idProperty->setValue($this, ++$id);
		$id--;

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
                if(!$this->core->getEssentials()->getNameBans()->isBanned($this->getName()) or !$this->core->getEssentials()->getIpBans()->isBanned($this->getName())) {
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

    public function handleEntityPickRequest(ActorPickRequestPacket $pk) : bool {
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
				$target = $this->level->getEntity($pk->target);

				$this->setTargetEntity($target);

				if($target instanceof Linkable) {
					$target->unlink();
				}
            break;
            case InteractPacket::ACTION_MOUSEOVER:
				$target = $this->level->getEntity($pk->target);

				$this->setTargetEntity($target);
				//TODO: Check distance
				if($target instanceof Lookable) {
					if($target instanceof Enderman and $this->getArmorInventory()->getHelmet() instanceof Pumpkin) {
						break;
					}
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
					if($this->level instanceof Level) {
						$target = $this->level->getEntity($pk->trData->entityRuntimeId);

						$this->setTargetEntity($target);
						$this->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, "");

						if($target instanceof Interactable) {
							$target->onPlayerInteract($this);
							return true;
							break;
						}
					}
                break;
            }
        }
        return $return;
    }
}