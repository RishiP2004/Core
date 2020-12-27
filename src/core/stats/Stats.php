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

use core\stats\command\{
	Accounts,
	AddPlayerPermission,
	BuyRank,
	DeleteAccount,
	GiveCoins,
	ListPlayerPermissions,
	PayCoins,
	PluginPermissions,
	Profile,
	RankInformation,
	Ranks,
	RemovePlayerPermission,
	Servers,
	SetCoins,
	SetRank,
	TakeCoins,
	TopCoins,
	UserInformation
};
use pocketmine\entity\Skin;

class Stats extends \core\utils\Manager implements Statistics {
   	public static $instance = null;

    public $ranks = [], $coreUsers = [], $allCoreUsers = [], $skinBounds = [];

    public $fallbackSkinData;
	
	public $user;

    public function init() {
    	self::$instance = $this;

        Core::getInstance()->saveResource("/stats/fallback.png");
		Core::getInstance()->saveResource("/stats/humanoid.json");

        $fallbackSkin = Entity::skinFromImage("fallback", Core::getInstance()->getDataFolder() . "/stats/fallback.png");

        if(!$fallbackSkin->isValid()) {
           $fallbackSkin = Entity::skinFromImage("fallback", Core::getInstance()->getDataFolder() . "/stats/fallback.png");
        }
        $this->fallbackSkinData = $fallbackSkin->getSkinData();
        $cubes = Entity::getCubes(json_decode(file_get_contents(Core::getInstance()->getDataFolder() . "/stats/humanoid.json"), true)["geometry.humanoid"]);
        $this->skinBounds[self::BOUNDS_64_64] = Entity::getSkinBounds($cubes);
        $this->skinBounds[self::BOUNDS_128_128] = Entity::getSkinBounds($cubes, 2.0);

        Core::getInstance()->getDatabase()->executeGeneric("stats.init");
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

		$this->registerCommand(Accounts::class, new Accounts($this));
		$this->registerCommand(AddPlayerPermission::class, new AddPlayerPermission($this));
		$this->registerCommand(BuyRank::class, new BuyRank($this));
		$this->registerCommand(DeleteAccount::class, new DeleteAccount($this));
		$this->registerCommand(GiveCoins::class, new GiveCoins($this));
		$this->registerCommand(ListPlayerPermissions::class, new ListPlayerPermissions($this));
		$this->registerCommand(PayCoins::class, new PayCoins($this));
		$this->registerCommand(PluginPermissions::class, new PluginPermissions($this));
		$this->registerCommand(Profile::class, new Profile($this));
		$this->registerCommand(RankInformation::class, new RankInformation($this));
		$this->registerCommand(Ranks::class, new Ranks($this));
		$this->registerCommand(RemovePlayerPermission::class, new RemovePlayerPermission($this));
		$this->registerCommand(Servers::class, new Servers($this));
		$this->registerCommand(SetCoins::class, new SetCoins($this));
		$this->registerCommand(SetRank::class, new SetRank($this));
		$this->registerCommand(TakeCoins::class, new TakeCoins($this));
		$this->registerCommand(TopCoins::class, new TopCoins($this));
		$this->registerCommand(UserInformation::class, new UserInformation($this));
    }

    public static function getInstance() : self {
    	return self::$instance;
	}

	public function getFallbackSkinData() {
        return $this->fallbackSkinData;
    }

    public function getStrippedSkin(Skin $skin) : Skin {
        $skinData = ($noCustomSkins = self::DISABLE_CUSTOM_SKINS === true) ? $this->fallbackSkinData : $skin->getSkinData();

        if(!$noCustomSkins && self::DISABLE_TRANSPARENT_SKINS === true && $this->getSkinTransparencyPercentage($skinData) > self::ALLOWED_TRANSPARECNY_PERCENTAGE) {
            $skinData = $this->fallbackSkinData;
        }
        $capeData = self::DISABLE_CUSTOM_CAPES === true ? "" : $skin->getCapeData();
        $geometryName = self::DISABLE_CUSTOM_GEOMETRY === true && $skin->getGeometryName() !== "geometry.humanoid.customSlim" ? "geometry.humanoid.custom" : $skin->getGeometryName();
        $geometryData = self::DISABLE_CUSTOM_GEOMETRY === true ? "" : $skin->getGeometryData();
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

	public function getAllCoins(callable $callback) : void {
		Core::getInstance()->getDatabase()->executeSelect("stats.topCoins", [], function(array $rows) use($callback) {
			$arr = [];

			foreach($rows as [
					"username" => $name,
					"coins" => $coins,
			]) {
				$arr = [
					$name => $coins
				];
			}
			$callback($arr);
		});
	}
	//TODO: Banned Players, OPs
    public function getTopCoins(int $pageSize, int $page) : array {
		$this->getAllCoins(function($coins) use($pageSize, $page) {
			asort($coins);
			$coins = array_chunk($coins, $pageSize, true); //DEFAULT SIZE SHOWN IS 5

			$page = min(count($coins), max(1, $page));

			return $coins[$page - 1] ?? [];
		});
		return [];
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
						return;
					}
				}
			}
		}
		$this->getDirectUser($string, $callback);
    }

	public function getDirectUser(string $string, callable $callback) : void {
    	Core::getInstance()->getDatabase()->executeSelect("stats.get", ['key' => $string], function(array $rows) use($callback) {
			if(count($rows) === 0) {
				$callback(null);
				return;
			}
			$data = $rows[0];
			$xuid = $data['xuid'];
			$coreUser = new CoreUser($xuid);

			$coreUser->load($data);
			$callback($coreUser);
		});
	}

	public function getAllCoreUsers(callable $callback) : void {
		Core::getInstance()->getDatabase()->executeSelect("stats.getAll", [], function(array $rows) use($callback) {
			$users = [];
			
			foreach($rows as [
				"xuid" => $xuid,
				"username" => $name,
				"coins" => $coins,
				"balance" => $balance,
				"permissions" => $permissions
            ]) {
				$coreUser = new CoreUser($xuid);
				$users[$xuid] = $coreUser;
				
				$coreUser->setName($name);
				$coreUser->setCoins($coins);
				$coreUser->setBalance($balance);
				$coreUser->setPermissions(unserialize($permissions));
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
		
       	Core::getInstance()->getDatabase()->executeInsert("stats.register", [
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
			$player->join($coreUser);
		}
    }

    public function unregisterCoreUser(CoreUser $user) {
        Core::getInstance()->getDatabase()->executeChange("stats.delete", [
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
}