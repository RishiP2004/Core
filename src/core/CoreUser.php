<?php

declare(strict_types = 1);

namespace core;

use core\anticheat\cheat\Cheat;

use core\network\server\Server;

use core\stats\rank\Rank;

use core\vote\task\Vote;

use pocketmine\permission\Permission;

class CoreUser {
	public $loaded = false;

    public $xuid = "", $name = "", $ip = "", $locale = "";

    public $registerDate;

    public $coins = 0, $balance = 0;
	/**
	 * @var Rank
	 */
    public $rank;
    /**
     * @var Server | null
     */
    public $server;

    public $permissions = [], $cheatHistory = [];

    public function __construct(string $xuid) {
        $this->xuid = $xuid;
    }

    public function load(array $data) {
		foreach($data as $field => $value) {
			$$field = $value;
		}
		$this->registerDate = $registerDate;
		$this->name = $username;
		$this->ip = $ip;
		$this->locale = $locale;
		$this->coins = $coins;
		$this->balance = $balance;
		$this->rank = Core::getInstance()->getStats()->getRank($rank);
		$this->permissions = [];
		$this->cheatHistory = [];
		$this->server = null;

		if(!is_null($permissions) && !is_null($cheatHistory)) {	
			$this->permissions = unserialize($permissions);
			$this->cheatHistory = unserialize($cheatHistory);
		}
		Core::getInstance()->getStats()->coreUsers[$this->getXuid()] = $this;

		$this->setLoaded();
    }

	public function loaded() : bool {
		return $this->loaded;
	}
	
	public function setLoaded(bool $loaded = true) {
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

    public function setName(string $name) {
        $this->name = $name;
    }

    public function getIp() : string {
        return $this->ip;
    }

    public function setIp(string $ip) {
        $this->ip = $ip;
    }
	
    public function getLocale() : string {
        return $this->locale;
    }

    public function setLocale(string $locale) {
        $this->locale = $locale;
    }

    public function getCoins() : int {
        return $this->coins;
    }

    public function setCoins(int $coins) {
        $this->coins = $coins;
    }

    public function getBalance() : int {
    	return $this->balance;
	}

	public function setBalance(int $balance) {
    	$this->balance = $balance;
	}
	
	public function getRank() : Rank {
    	if(is_null($this->rank)) {
    		foreach(Core::getInstance()->getStats()->getRanks() as $rank) {
    			if($rank->getValue() === Rank::DEFAULT) {
    				return $rank;
				}
			}
		}
		return $this->rank;
	}
	
	public function setRank(Rank $rank) {
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
		$player = Core::getInstance()->getServer()->getOfflinePlayer($this->getName());
		
		if($player->isOp()) {
			return true;
		}
		if(in_array("*", $this->getAllPermissions())) {
			return true;
		}
		return in_array($permission, $this->getAllPermissions());
    }

    public function setPermissions(array $permissions) {
        $this->permissions = $permissions;
		$player = Core::getInstance()->getServer()->getPlayer($this->getName());

		if($player instanceof CorePlayer) {
			$player->updatePermissions();
		}
    }

    public function addPermission(Permission $permission) {
        $permissions = array_merge($this->getPermissions(), [$permission->getName()]);

        $this->setPermissions($permissions);
    }

    public function removePermission(Permission $permission) {
		$perm = [$permission->getName()];
		$perms = array_diff($this->permissions, $perm);
		
        $this->setPermissions($perms);
    }

    public function getCheatHistory() : array {
    	return $this->cheatHistory;
	}

	public function setCheatHistory(Cheat $cheat, int $amount) {
		$this->cheatHistory[$cheat->getId()] = $amount;
	}
	
	public function addToCheatHistory(Cheat $cheat, int $amount) {
    	$this->cheatHistory[$cheat->getId()] += $amount;
	}

	public function subtractFromCheatHistory(Cheat $cheat, int $amount) {
		$this->cheatHistory[$cheat->getId()] -= $amount;
	}

	public function getServer() : ?Server {
    	if(is_null($this->server)) {
    		return null;
		}
		return $this->server;
	}

	public function setServer(?Server $server) {
		$this->server = $server;
	}

    public function vote() {
        Core::getInstance()->getVote()->addToQueue($this);
        Core::getInstance()->getServer()->getAsyncPool()->submitTask(new Vote($this->getName(), Core::getInstance()->getVote()->getAPIKey()));
    }

    public function save() {
		if(is_null($this->getServer())) {
			$server = null;
		} else {
			$server = $this->getServer()->getName();
		}
		Core::getInstance()->getDatabase()->executeChange("stats.update", [
		    "username" => $this->getName(),
            "ip" => $this->getIp(),
            "locale" => $this->getLocale(),
            "coins" => $this->getCoins(),
            "balance" => $this->getBalance(),
            "rank" => $this->getRank()->getName(),
            "permissions" => serialize($this->getPermissions()),
            "cheatHistory" => serialize($this->getCheatHistory()),
            "server" => $server,
			"xuid" => $this->getXuid()
        ]);
    }
	
	public function unload() {
		$this->save();
		unset(Core::getInstance()->getStats()->coreUsers[$this->getXuid()]);
	}
}