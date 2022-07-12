<?php

declare(strict_types = 1);

namespace core\player;

use core\Core;
use core\player\rank\{
	CompoundRank,
	DefaultRank,
	RankIds};

use core\database\Database;

use core\player\rank\Rank;
use core\player\command\{
	AccountsCommand,
	AddPlayerPermissionCommand,
	BuyRankCommand,
	CoinsCommand,
	DeleteAccountCommand,
	GiveCoinsCommand,
	ListPlayerPermissionsCommand,
	PayCoinsCommand,
	ProfileCommand,
	RankInformationCommand,
	RanksCommand,
	RemovePlayerPermissionCommand,
	ServersCommand,
	SetCoinsCommand,
	SetRankCommand,
	TakeCoinsCommand,
	TopCoinsCommand,
	UserInformationCommand
};
use core\utils\Manager;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

//Todo: Change to player sessions
class PlayerManager extends Manager implements Statistics, RankIds {
   	public static ?self $instance = null;

   	public DefaultRank $defaultRank;

    public array $ranks = [];

	public array $coreUsers = [];

    public array $compoundRankStache = [];

    public function init() : void {
    	self::$instance = $this;

        Database::get()->executeGeneric("player.init");
		$this->defaultRank = new DefaultRank();
		
		$hexcite = new Rank(
			"Hexcite",
			RankIds::HEXCITE,
			TextFormat::BOLD . TextFormat::BLUE,
			TextFormat::BOLD . TextFormat::BLUE . "HEXCITE" . TextFormat::RESET,
			TextFormat::BOLD . TextFormat::BLUE . "HEXCITE" . TextFormat::RESET . "{DISPLAY_NAME}" . TextFormat::LIGHT_PURPLE . ": " . TextFormat::GRAY . "{MESSAGE}",
			TextFormat::BOLD . TextFormat::BLUE . "HEXCITE" . TextFormat::RESET . "{DISPLAY_NAME}",
			[],
			$this->getRankByName("Universal"),
			rank\RankIds::DONATOR_RANK,
			2.5
		);
		$hexcite->setFreePrice(5000);
		$og = new Rank(
			"OG",
			RankIds::OG,
			TextFormat::BOLD . TextFormat::GOLD,
			TextFormat::BOLD . TextFormat::GOLD . "OG" . TextFormat::RESET,
			TextFormat::BOLD . TextFormat::GOLD . "OG" . TextFormat::RESET . "{DISPLAY_NAME}" . TextFormat::LIGHT_PURPLE . ": " . TextFormat::GRAY . "{MESSAGE}",
			TextFormat::BOLD . TextFormat::GOLD . "OG" . TextFormat::RESET . "{DISPLAY_NAME}",
			[],
			$this->getRankByName("Hexcite"),
			RankIds::DONATOR_RANK,
			1
		);
		$og->setFreePrice(10000);
		$staff = new Rank(
			"Staff",
			RankIds::STAFF,
			TextFormat::BOLD . TextFormat::DARK_PURPLE,
			TextFormat::BOLD . TextFormat::DARK_PURPLE . "STAFF" . TextFormat::RESET,
			TextFormat::BOLD . TextFormat::DARK_PURPLE . "STAFF" . TextFormat::RESET . "{DISPLAY_NAME}" . TextFormat::GREEN . ": " . TextFormat::GRAY . "{MESSAGE}",
			TextFormat::BOLD . TextFormat::DARK_PURPLE . "STAFF" . TextFormat::RESET . "{DISPLAY_NAME}",
			[],
			$this->getRankByName("Youtuber"),
			RankIds::STAFF_RANK,
			0
		);
		$administrator = new Rank(
        	"Administrator",
			RankIds::ADMINISTRATOR,
			TextFormat::BOLD . TextFormat::DARK_AQUA,
			TextFormat::BOLD . TextFormat::DARK_AQUA . "{ADMINISTRATOR}" . TextFormat::RESET,
			TextFormat::BOLD . TextFormat::DARK_AQUA . "{ADMINISTRATOR}" . TextFormat::RESET . "{DISPLAY_NAME}" . TextFormat::GREEN . ": " . TextFormat::GRAY . "{MESSAGE}",
			TextFormat::BOLD . TextFormat::DARK_AQUA . "{ADMINISTRATOR}" . TextFormat::RESET . "{DISPLAY_NAME}",
			[],
			$this->getRankByName("Staff"),
			RankIds::STAFF_RANK,
			0
		);
		$manager = new Rank(
        	"Manager",
			RankIds::MANAGER,
			TextFormat::BOLD . TextFormat::DARK_BLUE,
			TextFormat::BOLD . TextFormat::DARK_BLUE . "{MANAGER}" . TextFormat::RESET,
			TextFormat::BOLD . TextFormat::DARK_BLUE . "{MANAGER}" . TextFormat::RESET . "{DISPLAY_NAME}" . TextFormat::GREEN . ": " . TextFormat::GRAY . "{MESSAGE}",
			TextFormat::BOLD . TextFormat::DARK_BLUE . "{MANAGER}" . TextFormat::RESET . "{DISPLAY_NAME}",
			[],
			$this->getRankByName("Administrator"),
			RankIds::STAFF_RANK,
			0
		);
		$owner = new Rank(
			"Owner",
			RankIds::OWNER,
			TextFormat::BOLD . TextFormat::DARK_AQUA,
			TextFormat::BOLD . TextFormat::DARK_AQUA . "{OWNER}" . TextFormat::RESET,
			TextFormat::BOLD . TextFormat::DARK_AQUA . "{OWNER}" . TextFormat::RESET . "{DISPLAY_NAME}" . TextFormat::GREEN . ": " . TextFormat::GRAY . "{MESSAGE}",
			TextFormat::BOLD . TextFormat::DARK_AQUA . "{OWNER}" . TextFormat::RESET . "{DISPLAY_NAME}",
			[],
			$this->getRankByName("Manager"),
			RankIds::STAFF_RANK,
			0
		);
		$eonive = new Rank(
			"Eonive",
			RankIds::EONIVE,
			TextFormat::BOLD . TextFormat::AQUA,
			TextFormat::BOLD . TextFormat::AQUA . "EONIVE" . TextFormat::RESET,
			TextFormat::BOLD . TextFormat::AQUA . "EONIVE" . TextFormat::RESET . "{DISPLAY_NAME}" . TextFormat::LIGHT_PURPLE . ": " . TextFormat::GRAY . "{MESSAGE}",
			TextFormat::BOLD . TextFormat::AQUA . "EONIVE" . TextFormat::RESET . "{DISPLAY_NAME}",
			[
				"lobby.essential.staffpuncher",
				"core.essential.command.chat.vip"
			],
			$this->getRankByName("Hexcite"),
			RankIds::DONATOR_RANK,
			2
		);
		$eonive->setPaidPrice(5);
		$universal = new Rank(
			"Universal",
			RankIds::UNIVERSAL,
			TextFormat::BOLD . TextFormat::RED,
			TextFormat::BOLD . TextFormat::RED . "UNIVERSAL" . TextFormat::RESET,
			TextFormat::BOLD . TextFormat::RED . "UNIVERSAL" . TextFormat::RESET . "{DISPLAY_NAME}" . TextFormat::LIGHT_PURPLE . ": " . TextFormat::GRAY . "{MESSAGE}",
			TextFormat::BOLD . TextFormat::RED . "UNIVERSAL" . TextFormat::RESET . "{DISPLAY_NAME}",
			[
				"player.chat.time",
				"essential.command.fly"
			],
			$this->getRankByName("Eonive"),
			RankIds::DONATOR_RANK,
			1
		);
		$universal->setPaidPrice(10);
		$pixelated = new Rank(
			"Pixelated",
			RankIds::PIXELATED,
			TextFormat::BOLD . TextFormat::DARK_RED,
			TextFormat::BOLD . TextFormat::DARK_RED . "PIXELATED",
			TextFormat::BOLD . TextFormat::DARK_RED . "PIXELATED" . TextFormat::RESET . "{DISPLAY_NAME}" . TextFormat::LIGHT_PURPLE . ": " . TextFormat::GRAY . "{MESSAGE}",
			TextFormat::BOLD . TextFormat::DARK_RED . "PIXELATED" . TextFormat::RESET . "{DISPLAY_NAME}",
			[],
			$this->getRankByName("Universal"),
			RankIds::DONATOR_RANK,
			0
		);
		$pixelated->setPaidPrice(15);
		$athener = new Rank(
			"Athener",
			RankIds::ATHENER,
			TextFormat::BOLD . TextFormat::BLUE,
			TextFormat::BOLD . TextFormat::BLUE . "ATHENER" . TextFormat::RESET,
			TextFormat::BOLD . TextFormat::BLUE . "ATHENER" . TextFormat::RESET . "{DISPLAY_NAME}" . TextFormat::LIGHT_PURPLE . ": " . TextFormat::GRAY . "{MESSAGE}",
			TextFormat::BOLD . TextFormat::BLUE . "ATHENER" . TextFormat::RESET . "{DISPLAY_NAME}",
			[],
			$this->getRankByName("Pixelated"),
			RankIds::DONATOR_RANK,
			0
		);
		$athener->setPaidPrice(30);
		$youtube = new Rank(
			"YouTube",
			RankIds::YOUTUBE,
			TextFormat::BOLD . TextFormat::DARK_RED,
			TextFormat::BOLD . TextFormat::BLACK . "You" . TextFormat::DARK_RED . "Tube" . TextFormat::RESET,
			TextFormat::BOLD . TextFormat::BLACK . "You" . TextFormat::DARK_RED . "Tube" . TextFormat::RESET . "{DISPLAY_NAME}" . TextFormat::LIGHT_PURPLE . ": " . TextFormat::GRAY . "{MESSAGE}",
			TextFormat::BOLD . TextFormat::BLACK . "You" . TextFormat::DARK_RED . "Tube" . TextFormat::RESET . "{DISPLAY_NAME}",
			[],
			$this->getRankByName("Athener"),
			RankIds::STAFF_RANK,
			0
		);
		$this->initRank($hexcite);
		$this->initRank($og);
		$this->initRank($staff);
		$this->initRank($administrator);
        $this->initRank($manager);
		$this->initRank($owner);
		$this->initRank($eonive);
		$this->initRank($universal);
		$this->initRank($pixelated);
		$this->initRank($athener);
		$this->initRank($youtube);

		Server::getInstance()->getPluginManager()->registerEvents(new PlayerListener($this), Core::getInstance());

		$this->registerPermissions([
			"accounts.command" => [
				"default" => "op",
				"description" => "Check all registered Accounts"
			],
			"addplayerpermission.command" => [
				"default" => "op",
				"description" => "Add a Permission to a Player"
			],
			"coins.command" => [
				"default" => "true",
				"description" => "Coins command"
			],
			"deleteaccount.command" => [
				"default" => "op",
				"description" => "Delete a User's account"
			],
			"givecoins.command" => [
				"default" => "op",
				"description" => "Give a Player coins"
			],
			"listplayerpermission.command" => [
				"default" => "op",
				"description" => "List all permissions a player has"
			],
			"paycoins.command" => [
				"default" => "true",
				"description" => "Pay a Player coins"
			],
			"rank.command" => [
				"default" => "true",
				"description" => "See a Player's rank"
			],
			"rankinformation.command" => [
				"default" => "op",
				"description" => "Check information about Ranks"
			],
			"ranks.command" => [
				"default" => "true",
				"description" => "See all Ranks"
			],
			"removeplayerpermission.command" => [
				"default" => "op",
				"description" => "Remove a Permission from a Player"
			],
			"setcoins.command" => [
				"default" => "op",
				"description" => "Set a Player's coins"
			],
			"setrank.command" => [
				"default" => "op",
				"description" => "Set a Player's rank"
			],
			"takecoins.command" => [
				"default" => "op",
				"description" => "Take a Player coins"
			],
			"topcoins.command" => [
				"default" => "op",
				"description" => "See top coins"
			],
			"userinformation.command" => [
				"default" => "op",
				"description" => "See user information"
			],
		]);
		$this->registerCommands("player", 
			new AccountsCommand(Core::getInstance(), "accounts", "Check all registered Accounts", ["accs"]),
			new AddPlayerPermissionCommand(Core::getInstance(), "addplayerpermission", "Add a Permission to a Player", ["addpperm"]),
			//BuyRankCommand(Core::getInstance(), "buyrank", "Buy a free rank in-game")
			new CoinsCommand(Core::getInstance(), "coins", "Coins command", ["mycoins"]),
			new DeleteAccountCommand(Core::getInstance(), "deleteaccount", "Delete a User's account", ["delacc"]),
			new GiveCoinsCommand(Core::getInstance(), "givecoins", "Give a Player coins"),
			new ListPlayerPermissionsCommand(Core::getInstance(), "listplayerpermission", "List all permissions a player has", ["listpperm"]),
			new PayCoinsCommand(Core::getInstance(), "paycoins", "Pay a Player coins"),
			//new ProfileCommand(Core::getInstance(), "profile", "Check your Profile")
			new RankInformationCommand(Core::getInstance(), "rankinformation", "Check information about Ranks"),
			new RanksCommand(Core::getInstance(), "ranks", "See all Ranks"),
			new RemovePlayerPermissionCommand(Core::getInstance(), "removeplayerpermission", "Remove a Permission from a Player", ["removepperm"]),
			//new ServersCommand(Core::getInstance(), "servers", "Server selection")
			new SetCoinsCommand(Core::getInstance(), "setcoins", "Set a Player's coins"),
			new SetRankCommand(Core::getInstance(), "setrank", "Set a Player's rank"),
			new TakeCoinsCommand(Core::getInstance(), "takecoins", "Take a Player coins"),
			new TopCoinsCommand(Core::getInstance(), "topcoins", "See top coins"),
			new UserInformationCommand(Core::getInstance(), "userinformation", "See user information")
		);
    }

    public static function getInstance() : self {
    	return self::$instance;
	}

	public function getAllCoins(callable $callback) : void {
		Database::get()->executeSelect("player.allCoins", [], function(array $rows) use($callback) {
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

    public function initRank(Rank $rank) : void {
		$this->ranks[$rank->getValue()][$rank->getIdentifier()] = $rank;
    }

	public function getRank(int $identifier) : CompoundRank | DefaultRank | null {
		if($identifier === $this->defaultRank->getIdentifier()) {
			return $this->defaultRank;
		}
		if(isset($this->compoundRankStache[$identifier])) {
			return $this->compoundRankStache[$identifier];
		}
		$donatorId = $identifier & CompoundRank::DONATOR_RANK_MASK;
		$staffId = $identifier >> 8;

		if(!array_key_exists($donatorId, $this->ranks[RankIds::DONATOR_RANK]) && !array_key_exists($staffId, $this->ranks[RankIds::STAFF_RANK])) {
			return null;
		}
		$donatorRank = null;
		$staffRank = null;
		
		if(isset($this->ranks[RankIds::DONATOR_RANK][$donatorId])) {
			$donatorRank = $this->ranks[RankIds::DONATOR_RANK][$donatorId];
		}
		if(isset($this->ranks[RankIds::STAFF_RANK][$staffId])) {
			$staffRank = $this->ranks[RankIds::STAFF_RANK][$staffId];
		}
		return $this->compoundRankStache[$identifier] = new CompoundRank(
			$donatorRank,
			$staffRank
		);
	}

	public function getRanksFlat() : array {
		return $this->ranks[RankIds::DONATOR_RANK] + $this->ranks[RankIds::STAFF_RANK] + [$this->defaultRank];
	}

	public function getRankByName($rank) : DefaultRank | Rank | null {
		if ($rank === $this->defaultRank->getName() or is_null($rank)) {
			return $this->defaultRank;
		}
		foreach($this->ranks as $halfRankType) {
			foreach($halfRankType as $halfRank) {
				if($halfRank instanceof Rank && $halfRank->getName() === $rank) {
					return $halfRank;
				}
			}
		}
		return null;
	}

	public function getDefaultRank() : DefaultRank {
    	return $this->defaultRank;
	}

	public function getAllCoreUsers(callable $callback) : void {
		Database::get()->executeSelect("player.getAll", [], function(array $rows) use($callback) {
			$users = [];
			
			foreach($rows as [
				"xuid" => $xuid,
				"username" => $name,
				"coins" => $coins,
				"permissions" => $permissions
            ]) {
				$coreUser = new CoreUser($xuid);
				$users[$xuid] = $coreUser;
				
				$coreUser->setName($name);
				$coreUser->setCoins($coins);
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

    public function registerCoreUser(CorePlayer $player) : void {
		$name = $player->getName();
		$ip = $player->getNetworkSession()->getIp();
		$locale = $player->getPlayerInfo()->getLocale();
		
       	Database::get()->executeInsert("player.register", [
            "xuid" => $player->getXuid(),
            "registerDate" => date("m-d-Y g:iA"),
            "username" => $name,
            "ip" => $ip,
            "locale" => $locale
        ]);
		$coreUser = new CoreUser($player->getPlayerInfo()->getXuid());
		$user[$player->getPlayerInfo()->getXuid()] = $coreUser;
		$this->coreUsers[] = $user;
		
		$coreUser->setName($name);
		$coreUser->setIp($ip);
		$coreUser->setLocale($locale);
		$coreUser->setLoaded();
		
		if($coreUser->loaded()) {
			$player->join($coreUser);
		}
    }

    public function unregisterCoreUser(CoreUser $user) : void {
       Database::get()->executeChange("player.delete", [
            "xuid" => $user->getXuid()
        ]);
        unset($this->coreUsers[$user->getXuid()]);
    }
	
	public function saveUsers() : void {
		foreach($this->getCoreUsers() as $coreUser) {
			if($coreUser instanceof CoreUser) {
				$coreUser->save();
			}
		}
	}
	
	public function unloadUsers() : void {
		foreach($this->getCoreUsers() as $coreUser) {
			if($coreUser instanceof CoreUser) {
				$coreUser->unload();
			}
		}
	}
}