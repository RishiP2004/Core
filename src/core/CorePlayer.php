<?php

declare(strict_types = 1);

namespace core;

use core\utils\Item;

use core\broadcast\Broadcasts;
use core\broadcast\bossbar\Messages;

use core\essence\npc\NPC;
use core\essence\floatingtext\FloatingText;

use core\network\server\Server;

use core\stats\task\PlayerJoin;
use core\stats\Statistics;

use core\vote\VoteData;

use core\world\area\Area;

use form\{
	CustomFormResponse,
	MenuForm,
	CustomForm,
	ServerSettingsForm
};
use form\element\{
	Button,
	Image,
	Label
};

use pocketmine\Player;

use pocketmine\network\SourceInterface;

use pocketmine\network\mcpe\protocol\{
	SetPlayerGameTypePacket,
	ServerSettingsResponsePacket
};

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
	 * @var int|null
	 */
	protected $lastMovement = null;
	/** 
	 * @var PermissionAttachment 
	 */
	public $attachment;

    public function __construct(SourceInterface $interface, string $ip, int $port) {
		parent::__construct($interface, $ip, $port);
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
        $format = str_replace("{PREFIX}", $this->core::PREFIX, $format);
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
			$this->core::PREFIX,
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

	public function hasHud(int $type) : bool {
    	return $this->hud[$type] ?? isset($this->hud[$type]);
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
        $this->sendMessage($this->core::PREFIX . "Opened Servers menu");

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
					$server = $this->core->getNetwork()->getServer($button->getId());

					if($server instanceof Server) {
						if(!$player->hasPermission("core.network." . $server->getName())) {
							$player->sendMessage($this->core::ERROR_PREFIX . "You do not have Permission to use this Server");
						}
						if($server->isWhitelisted() && !$player->hasPermission("core.network." . $server->getName() . ".whitelist")) {
							$player->sendMessage($this->core::ERROR_PREFIX . $server->getName() . " is Whitelisted");
						} else {
							$player->transfer($server->getIp() . $server->getPort());
							$player->sendMessage($this->core::ERROR_PREFIX . "Transferring to the Server " . $server->getName());
						}
					}
				}
			},
			function(Player $player) : void {
				$player->sendMessage($this->core::PREFIX . "Closed Servers Form");
        }));
    }

    public function sendProfileForm(string $key = "profile", CoreUser $user = null) {
        switch($key) {
            case "profile":
                $this->sendMessage($this->core::PREFIX . "Opened Profile menu");

                $b1 = new Button(TextFormat::GRAY . "Global");

                $b1->setId(1);

                $b2 = new Button(TextFormat::GRAY . "Lobby", new Image($this->core->getNetwork()->getServer("Lobby")->getIcon(), Image::TYPE_URL));

                $b2->setId(2);

                $b3 = new Button(TextFormat::GRAY . "Survival", new Image($this->core->getNetwork()->getServer("Survival")->getIcon()));

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
									//$player->sendProfileForm("survival", $this->user);
								break;
							}
						}
					},
					function(Player $player) : void {
						$player->sendMessage($this->core::PREFIX . "Closed Profile menu");
					}
				));
            break;
            case "global":           
				$profile = "Your Profile";				
				$server = $this->getCoreUser()->getServer()->getName();
				$rank = $this->getCoreUser()->getRank()->getFormat();
				$coins = $this->getCoreUser()->getCoins();
				
				if(!is_null($user)) {
					if(!is_null($user->getServer())) {
						$server = $user->getServer()->getName();
					} else {
						$server = "Offline";
					}
					$rank = $user->getRank()->getFormat();
					$coins = $user->getCoins();
					$profile = $user->getName() . "'s Profile";
				} 
                $l1 = new Label(TextFormat::GRAY . "Rank: " . $rank);
                $l2 = new Label(TextFormat::GRAY . "Coins: " . Statistics::COIN_UNIT . $coins);
                $l4 = new Label(TextFormat::GRAY . "Server: " . $server);

                $data = [
                	$l1,
					$l2,
					$l4
                ];
				
                $this->sendForm(new CustomForm(TextFormat::GOLD . $profile . TextFormat::BLUE . " (Global)", $data, function(Player $player) : void {},
					function(Player $player) : void {
						$player->sendMessage($this->core::PREFIX . "Closed Profile menu");
					}
				));
            break;
        }
    }

	public function getServerSettingsForm(string $server = "lobby") : ServerSettingsForm {
		$elements = [
			new Label(TextFormat::GRAY . "Coming Soon!")
		];
		$image = new Image("http://icons.iconarchive.com/icons/double-j-design/diagram-free/128/settings-icon.png");
		$form = new ServerSettingsForm($this->core::PREFIX . "Athena Settings", $elements, $image,
			function(Player $player, CustomFormResponse $response) : void {

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
				$form = new ServerSettingsForm($this->core::PREFIX . "Athena Survival Settings", $elements, $image,
					function(Player $player, CustomFormResponse $response) : void {

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
		$this->sendMessage($this->core::PREFIX . "Thanks for Voting!");
		$this->core->getServer()->broadcastMessage($this->core::PREFIX . $this->getName() . " Voted for the Server and got rewarded!");
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
                $this->sendMessage($this->core::ERROR_PREFIX. "You cannot leave this area");
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
				$this->sendMessage($this->core::ERROR_PREFIX . "You cannot enter this area");
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

    public function sendSetting(CustomForm $form) {
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
                    $message = $this->core::PREFIX . "You have been Kicked\n" . TextFormat::GRAY . ($reason !== "" ? " Reason: " . $reason : "");
                }
            } else {
                if($reason === "") {
                    $message = $this->core::PREFIX . "You have been Kicked";
                }
            }
            $this->close($event->getQuitMessage(), $message);
            return true;
        }
        return false;
    }
}