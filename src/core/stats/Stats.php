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
	ListPlayerPermissions,
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

        $fallbackSkin = Entity::skinFromImage("fallback", $core->getDataFolder() . "/stats/fallback.png");

        if(!$fallbackSkin->isValid()) {
           $fallbackSkin = Entity::skinFromImage("fallback", $core->getDataFolder() . "/stats/fallback.png");
        }
        $this->fallbackSkinData = $fallbackSkin->getSkinData();
        $cubes = Entity::getCubes(json_decode(file_get_contents($core->getDataFolder() . "/stats/humanoid.json"), true)["geometry.humanoid"]);
        $this->skinBounds[self::BOUNDS_64_64] = Entity::getSkinBounds($cubes);
        $this->skinBounds[self::BOUNDS_128_128] = Entity::getSkinBounds($cubes, 2.0);

        $core->getDatabase()->executeGeneric("stats.init");
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
		$core->getServer()->getCommandMap()->register(Accounts::class, new Accounts($this->core));
		$core->getServer()->getCommandMap()->register(AddPlayerPermission::class, new AddPlayerPermission($this->core));
		$core->getServer()->getCommandMap()->register(BuyRank::class, new BuyRank($this->core));
		$core->getServer()->getCommandMap()->register(CurrencyChange::class, new CurrencyChange($this->core));
		$core->getServer()->getCommandMap()->register(DeleteAccount::class, new DeleteAccount($this->core));
		$core->getServer()->getCommandMap()->register(GiveBalance::class, new GiveBalance($this->core));
		$core->getServer()->getCommandMap()->register(GiveCoins::class, new GiveCoins($this->core));
		$core->getServer()->getCommandMap()->register(ListPlayerPermissions::class, new ListPlayerPermissions($this->core));
		$core->getServer()->getCommandMap()->register(PayBalance::class, new PayBalance($this->core));
		$core->getServer()->getCommandMap()->register(PayCoins::class, new PayCoins($this->core));
		$core->getServer()->getCommandMap()->register(PluginPermissions::class, new PluginPermissions($this->core));
		$core->getServer()->getCommandMap()->register(Profile::class, new Profile($this->core));
		$core->getServer()->getCommandMap()->register(RankInformation::class, new RankInformation($this->core));
		$core->getServer()->getCommandMap()->register(Ranks::class, new Ranks($this->core));
		$core->getServer()->getCommandMap()->register(RemovePlayerPermission::class, new RemovePlayerPermission($this->core));
		$core->getServer()->getCommandMap()->register(Servers::class, new Servers($this->core));
		$core->getServer()->getCommandMap()->register(SetBalance::class, new SetBalance($this->core));
		$core->getServer()->getCommandMap()->register(SetCoins::class, new SetCoins($this->core));
		$core->getServer()->getCommandMap()->register(SetRank::class, new SetRank($this->core));
		$core->getServer()->getCommandMap()->register(TakeBalance::class, new TakeBalance($this->core));
		$core->getServer()->getCommandMap()->register(TakeCoins::class, new TakeCoins($this->core));
		$core->getServer()->getCommandMap()->register(TopBalance::class, new TopBalance($this->core));
		$core->getServer()->getCommandMap()->register(TopCoins::class, new TopCoins($this->core));
		$core->getServer()->getCommandMap()->register(UserInformation::class, new UserInformation($this->core));
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

	public function sendTopEconomy(string $unit, CommandSender $sender, int $page, array $ops, array $banned) {
		$this->core->getStats()->getAllCoreUsers(function($users) use($unit, $sender, $page, $ops, $banned) {
			if(count($users) === 0) {
				$sender->sendMessage($this->core->getErrorPrefix() . "No Accounts registered");
				return;
			}
			$allEconomy = [];
			
			foreach($users as $user) {
				if($unit === "coins") {
					$allEconomy[$user->getName()] = $user->getCoins();
				} else if($unit === "balance") {
					$allEconomy[$user->getName()] = $user->getBalance();
				}
			}
			$this->core->getServer()->getAsyncPool()->submitTask(new TopEconomy($sender->getName(), $unit, $allEconomy, $page, self::ADD_OPS, $ops, self::ADD_BANNED, $banned));
		});
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
		$this->core->getDatabase()->executeSelect("stats.get", ['key' => $string], function(array $rows) use($callback) {
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
		$this->core->getDatabase()->executeSelect("stats.getAll", [], function(array $rows) use($callback) {
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
			$player->join($coreUser);
		}
    }

    public function unregisterCoreUser(CoreUser $user) {
        $this->core->getDatabase()->executeChange("stats.delete", [
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