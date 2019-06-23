<?php

declare(strict_types = 1);

namespace core;

use core\anticheat\cheat\Cheat;

use core\network\server\Server;

use core\stats\rank\Rank;

use core\vote\task\VoteTask;

class CoreUser {
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

        $this->save();
    }

    public function load(array $data) {
        $this->registerDate = $data["registerDate"];
        $this->name = $data["username"];
        $this->ip = $data["ip"];
        $this->locale = $data["locale"];
        $this->coins = $data["coins"];
        $this->balance = $data["balance"];
        $this->rank = Core::getInstance()->getStats()->getRank($data["rank"]);
        $this->permissions = $data["permissions"];
        $this->cheatHistory = unserialize($data["cheatHistory"]);
        $this->server = Core::getInstance()->getNetwork()->getServer($data["server"]);
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

    public function getLocale() : string {
        return $this->locale;
    }

    public function setLocale(string $locale) {
        $this->locale = $locale;
    }

    public function getIp() : string {
        return $this->ip;
    }

    public function setIp(string $ip) {
        $this->ip = $ip;
    }
	
    public function getServer() : ?Server {
        return $this->server;
    }

    public function setServer(?Server $server) {
        $this->server = $server;
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
		return $this->rank;
	}
	
	public function setRank(Rank $rank) {
		$this->rank = $rank;
	}
	
    public function getAllPermissions() : array {
        return array_merge($this->getRank()->getPermissions(), $this->getPermissions());
    }

    public function getPermissions() : array {
        return $this->permissions;
    }

    public function hasDatabasedPermission(string $permission) {
        return in_array($permission, $this->getPermissions());
    }

    public function setPermission(array $permissions) {
        $this->permissions = $permissions;
    }

    public function addPermission(string $permission) {
        $permissions = $this->getPermissions();
        $permissions = array_merge($permissions, [$permission]);

        $this->setPermission($permissions);
    }

    public function removePermission(string $permission) {
        $permissions = $this->getPermissions();

        unset($permissions[$permission]);
        $this->setPermission($permissions);
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

    public function vote() {
        Core::getInstance()->getVote()->addToQueue($this);
        Core::getInstance()->getServer()->getAsyncPool()->submitTask(new VoteTask($this->getName(), Core::getInstance()->getVote()->getAPIKey()));
    }

    public function save() {
		Core::getInstance()->getDatabase()->executeChange("stats.update", [
		    "username" => $this->getName(),
            "ip" => $this->getIp(),
            "locale" => $this->getLocale(),
            "coins" => $this->getCoins(),
            "balance" => $this->getBalance(),
            "rank" => $this->getRank(),
            "permissions" => $this->getPermissions(),
            "cheatHistory" => serialize($this->getCheatHistory()),
            "server" => $this->getServer()
        ]);
    }
}