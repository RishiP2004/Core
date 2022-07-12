<?php

declare(strict_types = 1);

namespace core\player;

use core\Core;

use core\anticheat\cheat\Cheat;
use core\database\Database;
use core\network\server\Server;
use pocketmine\nbt\tag\CompoundTag;
use core\player\rank\{
	CompoundRank,
	Rank,
	DefaultRank};

use core\vote\task\VoteTask;
use core\vote\VoteData;
use pocketmine\network\mcpe\protocol\types\entity\Vec3MetadataProperty;
use pocketmine\permission\Permission;

class CoreUser {
	public bool $loaded = false;

    public string $name = "", $ip = "", $locale = "";

    public string $registerDate;

    public int $coins = 0;

    public Rank|DefaultRank $rank;

    public ?Server $server;

    public array $permissions = [], $cheatHistory = [];

    public bool $dm = true;

    public function __construct(private string $xuid) {}

    public function load(array $data) : void {
		foreach($data as $field => $value) {
			$$field = $value;
		}
		$this->registerDate = $registerDate;
		$this->name = $username;
		$this->ip = $ip;
		$this->locale = $locale;
		$this->coins = $coins;
		$this->rank = PlayerManager::getInstance()->getRank((int) $rank);
		$this->permissions = [];
		$this->cheatHistory = [];
		$this->server = null;
		$this->dm = (bool) $dm;

		if(!is_null($permissions) && !is_null($cheatHistory)) {	
			$this->permissions = unserialize($permissions);
			$this->cheatHistory = unserialize($cheatHistory);
		}
		PlayerManager::getInstance()->coreUsers[$this->getXuid()] = $this;

		$this->setLoaded();
    }

	public function loaded() : bool {
		return $this->loaded;
	}
	
	public function setLoaded(bool $loaded = true) : void {
		$this->loaded = $loaded;
	}
	
    public function getXuid() : string {
        return $this->xuid;
    }

    public function getRegisterDate() : string {
        return $this->registerDate;
    }

    public function getName() : string {
        return $this->name;
    }

    public function setName(string $name) : void {
        $this->name = $name;
    }

    public function getIp() : string {
        return $this->ip;
    }

    public function setIp(string $ip) : void {
        $this->ip = $ip;
    }
	
    public function getLocale() : string {
        return $this->locale;
    }

    public function setLocale(string $locale) : void {
        $this->locale = $locale;
    }

    public function getCoins() : int {
        return $this->coins;
    }

    public function setCoins(int $coins) : void {
        $this->coins = $coins;
    }
	
	public function getRank() : Rank|DefaultRank {
		return $this->rank;
	}
	
	public function setRank(Rank|DefaultRank|CompoundRank $rank) : void {
		$this->rank = $rank;
	}

	public function getAllPermissions() : array {
		return array_merge($this->getRank()->getPermissions(), $this->permissions);
	}
	
    public function getPermissions() : array {
        return $this->permissions;
    }

    public function hasPermission(string $permission) : bool {
		if($permission instanceof Permission) {
			$permission = $permission->getName();
		}
		if(\pocketmine\Server::getInstance()->isOp($this->getName())) {
			return true;
		}
		if(in_array("*", $this->getAllPermissions())) {
			return true;
		}
		return in_array($permission, $this->getAllPermissions());
    }

    public function setPermissions(array $permissions) : void {
        $this->permissions = $permissions;
		$player = Core::getInstance()->getServer()->getPlayerByPrefix($this->getName());

		if($player instanceof CorePlayer) {
			$player->updatePermissions();
		}
    }

    public function addPermission(Permission $permission) : void {
        $permissions = array_merge($this->getPermissions(), [$permission->getName()]);

        $this->setPermissions($permissions);
    }

    public function removePermission(Permission $permission) : void {
		$perm = [$permission->getName()];
		$perms = array_diff($this->permissions, $perm);
		
        $this->setPermissions($perms);
    }

    public function getCheatHistory() : array {
    	return $this->cheatHistory;
	}

	public function getCheatHistoryFor(Cheat $cheat) : int {
    	return $this->cheatHistory[$cheat->getId()];
	}

	public function setCheatHistory(Cheat $cheat, int $amount) : void {
		$this->cheatHistory[$cheat->getId()] = $amount;
	}
	
	public function addToCheatHistory(Cheat $cheat, int $amount) : void {
    	$this->cheatHistory[$cheat->getId()] += $amount;
	}

	public function subtractFromCheatHistory(Cheat $cheat, int $amount) : void {
		$this->cheatHistory[$cheat->getId()] -= $amount;
	}

	public function getServer() : ?Server {
    	if(is_null($this->server)) {
    		return null;
		}
		return $this->server;
	}

	public function setServer(?Server $server) : void {
		$this->server = $server;
	}

    public function vote() : void {
        VoteTask::getInstance()->addToQueue($this);
        Core::getInstance()->getServer()->getAsyncPool()->submitTask(new VoteTask($this->getName(), VoteData::API_KEY));
    }

    public function hasDM() : bool {
    	return $this->dm;
	}

    public function toggleDM(bool $dm) : void {
    	$this->dm = $dm;
	}

    public function save() : void {
		if(is_null($this->getServer())) {
			$server = null;
		} else {
			$server = $this->getServer()->getName();
		}
		Database::get()->executeChange("player.update", [
		    "username" => $this->getName(),
            "ip" => $this->getIp(),
            "locale" => $this->getLocale(),
            "coins" => $this->getCoins(),
            "rank" => $this->getRank()->getName(),
            "permissions" => serialize($this->getPermissions()),
            "cheatHistory" => serialize($this->getCheatHistory()),
            "server" => $server,
			"dm" => (int)$this->hasDM(),
			"xuid" => $this->getXuid()
        ]);
    }
	
	public function unload() : void {
		$this->save();
		unset(PlayerManager::getInstance()->coreUsers[$this->getXuid()]);
	}
}