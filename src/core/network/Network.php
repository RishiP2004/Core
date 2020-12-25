<?php

declare(strict_types = 1);

namespace core\network;

use core\Core;

use core\stats\Stats;

use core\utils\{
	Manager,
	Math
};

use core\network\server\{
    Server,
    Factions,
    Lobby
};
use core\network\command\{
	Backup,
	Restarter
};
use core\network\thread\{
	Compress,
	Restore
};

class Network extends Manager implements Networking {
	public static $instance = null;

    private $timer;

    public $servers = [];

    const CHAT = 0;
    const POPUP = 1;
    const TITLE = 2;

    private $runs = 0;

    public function init() {
    	self::$instance = $this;
        $this->timer = new Timer();

        $this->initServer(new Factions());
        $this->initServer(new Lobby());
        $this->registerCommand(Backup::class, new Backup($this));
        $this->registerCommand(Restarter::class, new Restarter($this));
        $this->registerListener(new NetworkListener($this), Core::getInstance());
    }

    public static function getInstance() : self {
		return self::$instance;
	}

	public function getTimer() : Timer {
        return $this->timer;
    }

    public function tick() : void {
    	$this->runs++;

		if(is_int(self::RESTART)) {
			if($this->runs === self::RESTART * 60) {
				if(!$this->getTimer()->isPaused()) {
					$this->getTimer()->subtractTime(1);

					if($this->getTimer()->getTime() <= self::COUNTDOWN_START) {
						$this->getTimer()->broadcastTime(self::MESSAGES["countdown"], self::DISPLAY_TYPE);
					}
					if($this->getTimer()->getTime() < 1) {
						$this->getTimer()->initiateRestart(Timer::NORMAL);
					}
				}
			}
		}
		if(is_int(self::SERVER_SAVE)) {
			if($this->runs === self::SERVER_SAVE * 60) {
				$this->compress();
				Stats::getInstance()->saveUsers();
			}
		}
		if(self::RESTART_ON_OVERLOAD) {
			if($this->runs === 6000) {
				if(Math::isOverloaded(self::MEMORY_LIMIT)) {
					$this->getTimer()->initiateRestart(Timer::OVERLOADED);
				}
			}
		}
		if(is_int(self::BROADCAST_INTERVAL)) {
			if($this->runs === self::BROADCAST_INTERVAL) {
				if(!$this->getTimer()->isPaused()) {
					if($this->getTimer()->getTime() >= self::COUNTDOWN_START) {
						$this->getTimer()->broadcastTime(self::MESSAGES["broadcast"], self::DISPLAY_TYPE);
					}
				}
			}
		}
	}

	public function compress() {
		new Compress(realpath(Core::getInstance()->getDataFolder()), realpath(Core::getInstance()->getDataFolder() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'));
	}

	public function restore() {
		new Restore(realpath(Core::getInstance()->getDataFolder()), realpath(Core::getInstance()->getDataFolder() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..'));
		$this->getTimer()->initiateRestart(Timer::NORMAL);
	}

    public function initServer(Server $server) {
        $this->servers[$server->getName()] = $server;
    }
    /**
     * @return Server[]
     */
    public function getServers() : array {
        return $this->servers;
    }

    public function getServer(string $server) : ?Server {
        $lowerKeys = array_change_key_case($this->servers, CASE_LOWER);

        if(isset($lowerKeys[strtolower($server)])) {
            return $lowerKeys[strtolower($server)];
        }
        return null;
    }

    public function getServerFromIp(string $ip) : Server {
        foreach($this->getServers() as $server) {
            if($server->getIp() === $ip) {
                return $this->getServer($server->getName());
            }
        }
        return new Lobby();
    }

    public function getTotalMaxSlots() : int {
        foreach($this->getServers() as $server) {
            if($server instanceof Server) {
                return \pocketmine\Server::getInstance()->getMaxPlayers() + $server->getMaxSlots();
            }
        }
        return 0;
    }

    public function getTotalOnlinePlayers() : array {
        foreach($this->getServers() as $server) {
            if($server instanceof Server) {
                return array_merge($server->getOnlinePlayers(), \pocketmine\Server::getInstance()->getOnlinePlayers());
            }
        }
        return [];
    }
}