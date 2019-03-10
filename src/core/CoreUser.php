<?php

namespace core;

use core\network\server\Server;

use core\stats\rank\Rank;

use core\vote\ServerListQuery;

use core\vote\task\RequestThreadTask;

class CoreUser {
    const CORE = 0;
    const LOBBY = 1;
    const FACTIONS = 2;

    public $xuid = "", $registerDate, $name = "", $ip = "", $locale = "";

    public $coins;

    public $rank;
    /**
     * @var Server | null
     */
    public $server;

    public $permissions = [];

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
        $this->rank = Core::getInstance()->getStats()->getRank($data["rank"]);
        $this->permissions = $data["permissions"];
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

    public function vote() {
        Core::getInstance()->getVote()->addToQueue($this);
        $requests = [];

        foreach(Core::getInstance()->getVote()->getLists() as $list) {
            if(isset($list["Check"]) && isset($list["Claim"])) {
                $requests[] = new ServerListQuery($list["Check"], $list["Claim"]);
            }
        }
        $query = new RequestThreadTask($this->getName(), $requests);

        Core::getInstance()->getServer()->getAsyncPool()->submitTask($query);
    }

    public function save() {
		Core::getInstance()->getDatabase()->executeChange("stats.update", [
		    "username" => $this->getName(),
            "ip" => $this->getIp(),
            "locale" => $this->getLocale(),
            "coins" => $this->getCoins(),
            "rank" => $this->getRank(),
            "permissions" => $this->getPermissions(),
            "server" => $this->getServer()
        ]);
    }
}