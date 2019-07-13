<?php

declare(strict_types = 1);

namespace core\stats;

use core\Core;
use core\CorePlayer;
use core\CoreUser;

use core\utils\Entity;

use core\stats\rank\{
    Rank,
    Administrator,
    Athener,
    Eonive,
    Hexcite,
    Manager,
    OG,
    Owner,
    Pixelated,
    Player,
    Staff,
    Universal,
    YouTuber
};
use core\stats\task\{
	AFKSetter,
	TopEconomy
};
use core\stats\command\{
	Accounts,
	AddPlayerPermission,
	BuyRank,
	CurrencyChange,
	DeleteAccount,
	GiveBalance,
	GiveCoins,
	PayBalance,
	PayCoins,
	PluginPermissions,
	Profile,
	RankInformation,
	Ranks,
	RemovePlayerPermission,
	Servers,
	SetBalance,
	SetCoins,
	SetPlayerPermission,
	SetRank,
	TakeBalance,
	TakeCoins,
	TopBalance,
	TopCoins,
	UserInformation
};

use pocketmine\command\CommandSender;

use pocketmine\entity\Skin;

class Stats implements Statistics {
    private $core;

    public $ranks = [], $coreUsers = [], $allCoreUsers = [], $skinBounds = [];

    public $fallbackSkinData;
	
	public $user;

    public function __construct(Core $core) {
        $this->core = $core;

        $core->saveResource("/stats/fallback.png");
		$core->saveResource("/stats/humanoid.json");

        $fallbackSkin = new Skin("fallback", Entity::skinFromImage($core->getDataFolder() . "/stats/fallback.png"));

        if(!$fallbackSkin->isValid()) {
            $fallbackSkin = new Skin('fallback', Entity::skinFromImage($core->getDataFolder() . "/stats/fallback.png"));
        }
        $this->fallbackSkinData = $fallbackSkin->getSkinData();
        $cubes = Entity::getCubes(json_decode(file_get_contents($core->getDataFolder() . "/stats/humanoid.json"), true)["geometry.humanoid"]);
        $this->skinBounds[self::BOUNDS_64_64] = Entity::getSkinBounds($cubes);
        $this->skinBounds[self::BOUNDS_128_128] = Entity::getSkinBounds($cubes, 2.0);

        $this->core->getDatabase()->executeGeneric("stats.init");
        $this->initRank(new Administrator());
        $this->initRank(new Athener());
        $this->initRank(new Eonive());
        $this->initRank(new Hexcite());
        $this->initRank(new Manager());
        $this->initRank(new OG());
        $this->initRank(new Owner());
        $this->initRank(new Pixelated());
        $this->initRank(new Player());
        $this->initRank(new Staff());
        $this->initRank(new Universal());
        $this->initRank(new YouTuber());
        $this->scheduleAFKSetter();
		$this->core->getServer()->getCommandMap()->register(Accounts::class, new Accounts($this->core));
		$this->core->getServer()->getCommandMap()->register(AddPlayerPermission::class, new AddPlayerPermission($this->core));
		$this->core->getServer()->getCommandMap()->register(BuyRank::class, new BuyRank($this->core));
		$this->core->getServer()->getCommandMap()->register(CurrencyChange::class, new CurrencyChange($this->core));
		$this->core->getServer()->getCommandMap()->register(DeleteAccount::class, new DeleteAccount($this->core));
		$this->core->getServer()->getCommandMap()->register(GiveBalance::class, new GiveBalance($this->core));
		$this->core->getServer()->getCommandMap()->register(GiveCoins::class, new GiveCoins($this->core));
		$this->core->getServer()->getCommandMap()->register(PayBalance::class, new PayBalance($this->core));
		$this->core->getServer()->getCommandMap()->register(PayCoins::class, new PayCoins($this->core));
		$this->core->getServer()->getCommandMap()->register(PluginPermissions::class, new PluginPermissions($this->core));
		$this->core->getServer()->getCommandMap()->register(Profile::class, new Profile($this->core));
		$this->core->getServer()->getCommandMap()->register(RankInformation::class, new RankInformation($this->core));
		$this->core->getServer()->getCommandMap()->register(Ranks::class, new Ranks($this->core));
		$this->core->getServer()->getCommandMap()->register(RemovePlayerPermission::class, new RemovePlayerPermission($this->core));
		$this->core->getServer()->getCommandMap()->register(Servers::class, new Servers($this->core));
		$this->core->getServer()->getCommandMap()->register(SetBalance::class, new SetBalance($this->core));
		$this->core->getServer()->getCommandMap()->register(SetCoins::class, new SetCoins($this->core));
		$this->core->getServer()->getCommandMap()->register(SetPlayerPermission::class, new SetPlayerPermission($this->core));
		$this->core->getServer()->getCommandMap()->register(SetRank::class, new SetRank($this->core));
		$this->core->getServer()->getCommandMap()->register(TakeBalance::class, new TakeBalance($this->core));
		$this->core->getServer()->getCommandMap()->register(TakeCoins::class, new TakeCoins($this->core));
		$this->core->getServer()->getCommandMap()->register(TopBalance::class, new TopBalance($this->core));
		$this->core->getServer()->getCommandMap()->register(TopCoins::class, new TopCoins($this->core));
		$this->core->getServer()->getCommandMap()->register(UserInformation::class, new UserInformation($this->core));
    }

    public function getCoinValue() : int {
    	return self::COIN_VALUE;
	}

    public function getEconomyUnit(string $type) : string {
        return self::UNITS[$type];
    }

    public function getDefaultEconomy(string $type) : int {
        return self::DEFAULTS[$type];
    }

    public function getMaximumEconomy(string $type) : int {
        return self::MAXIMUMS[$type];
    }

    public function getTopShownPerPage(string $type) : int {
        return self::TOP_SHOWN_PER_PAGE[$type];
    }

    public function disableCustomSkins() : bool {
        return self::DISABLE_CUSTOM_SKINS;
    }

    public function disableCustomCapes() : bool {
        return self::DISABLE_CUSTOM_CAPES;
    }

    public function disableCustomGeometry() : bool {
        return self::DISABLE_CUSTOM_GEOMETRY;
    }

    public function disableIngameSkinChange() : bool {
        return self::DISABLE_INGAME_SKIN_CHANGE;
    }

    public function disableTransparentSkins() : bool {
        return self::DISABLE_TRANSPARENT_SKINS;
    }

    public function allowedTransparencyPercentage() : int {
        return self::ALLOWED_TRANSPARECNY_PERCENTAGE;
    }

    public function getAFKAutoSet() : int {
        return self::AFK_AUTO_SET;
    }

    public function getAFKAutoKick() : int {
        return self::AFK_AUTO_KICK;
    }

    public function getFallbackSkinData() {
        return $this->fallbackSkinData;
    }

    public function getStrippedSkin(Skin $skin) : Skin {
        $skinData = ($noCustomSkins = $this->disableCustomSkins() === true) ? $this->fallbackSkinData : $skin->getSkinData();

        if(!$noCustomSkins && $this->disableTransparentSkins() === true && $this->getSkinTransparencyPercentage($skinData) > $this->allowedTransparencyPercentage()) {
            $skinData = $this->fallbackSkinData;
        }
        $capeData = $this->disableCustomCapes() === true ? "" : $skin->getCapeData();
        $geometryName = $this->disableCustomGeometry() === true && $skin->getGeometryName() !== "geometry.humanoid.customSlim" ? "geometry.humanoid.custom" : $skin->getGeometryName();
        $geometryData = $this->disableCustomGeometry() === true ? "" : $skin->getGeometryData();
        return new Skin($skin->getSkinId(), $skinData, $capeData, $geometryName, $geometryData);
    }

    public function getSkinTransparencyPercentage(string $skinData) {
        switch(strlen($skinData)) {
            case 8192:
                $maxX = 64;
                $maxY = 32;
                $bounds = $this->skinBounds[self::BOUNDS_64_32];
            break;
            break;
            case 16384:
                $maxX = 64;
                $maxY = 64;
                $bounds = $this->skinBounds[self::BOUNDS_64_64];
            break;
            case 65536:
                $maxX = 128;
                $maxY = 128;
                $bounds = $this->skinBounds[self::BOUNDS_128_128];
            break;
            default:
                throw new \InvalidArgumentException("Inappropriate skin data length: " . strlen($skinData));
        }
        $transparentPixels = $pixels = 0;

        foreach($bounds as $bound) {
            if($bound["max"]["x"] > $maxX || $bound["max"]["y"] > $maxY) {
                continue;
            }
            for($y = $bound["min"]["y"]; $y <= $bound["max"]["y"]; $y++) {
                for($x = $bound["min"]["x"]; $x <= $bound["max"]["x"]; $x++) {
                    $key = (($maxX * $y) + $x) * 4;
                    $a = ord($skinData[$key + 3]);

                    if($a < 127) {
                        ++$transparentPixels;
                    }
                    ++$pixels;
                }
            }
        }
        return round($transparentPixels * 100 / max(1, $pixels));
    }

	public function sendTopEconomy(string $unit, CommandSender $sender, int $page, array $ops, array $banned) {
		$allEconomy = [];

		foreach($this->getCoreUsers() as $user) {
			if($unit === "coins") {
				$allEconomy[$user->getName()] = $user->getCoins();
			} else if($unit === "balance") {
				$allEconomy[$user->getName()] = $user->getBalance();
			}
		}
		$this->core->getServer()->getAsyncPool()->submitTask(new TopEconomy($sender->getName(), $unit, $allEconomy, $page, $ops, $banned));
	}

    public function initRank(Rank $rank) {
        $this->ranks[$rank->getName()] = $rank;
    }
    /**
     * @return Rank[]
     */
    public function getRanks() : array {
        return $this->ranks;
    }

    public function getRank(string $rank) : ?Rank {
        $lowerKeys = array_change_key_case($this->ranks, CASE_LOWER);

        if(isset($lowerKeys[strtolower($rank)])) {
            return $lowerKeys[strtolower($rank)];
        }
        return null;
    }

    public function getCoreUser(string $string, callable $callback) : void {
		if(!empty($this->getCoreUsers())) {
			foreach($this->getCoreUsers() as $coreUser) {
				if($coreUser instanceof CoreUser) {
					if($coreUser->getXuid() === $string or $coreUser->getName() === $string) {
						$callback($coreUser);
					} else {
						$this->getDirectUser($string, $callback);
					}
				}
			}
		}

		$this->getDirectUser($string, $callback);
    }

	public function getDirectUser(string $string, callable $callback) : void {
		$this->core->getDatabase()->executeSelect("stats.get", ['key' => $string], function(array $rows) use($callback) {
			foreach($rows as [
				"xuid" => $xuid,
				"username" => $name
			]) {
				$coreUser = new CoreUser($xuid);
				$users[$xuid] = $coreUser;

				$coreUser->load($rows);
				$callback($coreUser);
			}
		});
	}

	public function getAllCoreUsers(callable $callback) : void {
		$this->core->getDatabase()->executeSelect("stats.getAll", [], function(array $rows) use($callback) {
			$users = [];
			
			foreach($rows as [
                "xuid" => $xuid,
				"username" => $name
            ]) {
				$coreUser = new CoreUser($xuid);
				$users[$xuid] = $coreUser;
				
				$coreUser->setName($name);
			}
			$callback($users);
        });
	}
    /**
     * @return CoreUser[]
     */
    public function getCoreUsers() : array {
        return $this->coreUsers;
    }

    public function registerCoreUser(CorePlayer $player) {
		$name = $player->getName();
		$ip = $player->getAddress();
		$locale = $player->getLocale();
		
        $this->core->getDatabase()->executeInsert("stats.register", [
            "xuid" => $player->getXuid(),
            "registerDate" => date("m:d:y h:A"),
            "username" => $name,
            "ip" => $ip,
            "locale" => $locale
        ]);
		$coreUser = new CoreUser($player->getXuid());
		$user[$player->getXuid()] = $coreUser;
		$this->coreUsers[] = $user;
		
		$coreUser->setName($name);
		$coreUser->setIp($ip);
		$coreUser->setLocale($locale);
		$coreUser->setLoaded();
		
		if($coreUser->loaded()) {
			$player->join();
		}
    }

    public function unregisterCoreUser(CoreUser $user) {
        $this->core->getDatabase()->executeChange("stats.unregister", [
            "xuid" => $user->getXuid()
        ]);
        unset($this->coreUsers[$user->getXuid()]);
    }
	
	public function saveUsers() {
		foreach($this->getCoreUsers() as $coreUser) {
			if($coreUser instanceof CoreUser) {
				$coreUser->save();
			}
		}
	}
	
	public function unloadUsers() {
		foreach($this->getCoreUsers() as $coreUser) {
			if($coreUser instanceof CoreUser) {
				$coreUser->unload();
			}
		}
	}

    public function scheduleAFKSetter() {
        if($this->getAFKAutoSet() > 0) {
            $this->core->getScheduler()->scheduleDelayedTask(new AFKSetter($this->core), 600);
        }
    }
}