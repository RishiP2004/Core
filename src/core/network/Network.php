<?php

declare(strict_types = 1);

namespace core\network;

use core\Core;

use core\network\server\{
    Server,
    Factions,
    Lobby
};
use core\network\command\{
	Backup,
	Restarter
};

use core\utils\Math;

class Network implements Networking {
    private $core;

    private $timer;

    public $servers = [];

    const CHAT = 0;
    const POPUP = 1;
    const TITLE = 2;

    private $runs = 0;

    public function __construct(Core $core) {
        $this->core = $core;
        $this->timer = new Timer();

        $this->initServer(new Factions());
        $this->initServer(new Lobby());
        $core->getServer()->getCommandMap()->register("backup", new Backup($core));
        $core->getServer()->getCommandMap()->register("restarter", new Restarter($core));
    }

    public function getTimer() : Timer {
        return $this->timer;
    }

    public function getRestart() : int {
    	return self::RESTART;
	}

	public function getServerBackup() : int {
		return self::SERVER_BACKUP;
	}

	public function getCountdownStart() : int {
    	return self::COUNTDOWN_START;
	}

    public function getMemoryLimit() : string {
        return self::MEMORY_LIMIT;
    }

    public function getDisplayType() : int {
        return self::DISPLAY_TYPE;
    }

    public function restartOnOverload() : bool {
        return self::RESTART_ON_OVERLOAD;
    }

    public function getBroadcastInterval() : int {
    	return self::BROADCAST_INTERVAL;
	}

    public function getMessages(string $key) {
        return self::MESSAGES[$key];
    }

    public function tick() {
    	$this->runs++;

		if(is_int($this->getRestart())) {
			if($this->runs === $this->getRestart() * 60) {
				if(!$this->getTimer()->isPaused()) {
					$this->getTimer()->subtractTime(1);

					if($this->getTimer()->getTime() <= $this->getCountdownStart()) {
						$this->getTimer()->broadcastTime($this->getMessages("countdown"), $this->getDisplayType());
					}
					if($this->getTimer()->getTime() < 1) {
						$this->getTimer()->initiateRestart(Timer::NORMAL);
					}
				}
			}
		}
		if(is_int($this->getServerBackup())) {
			if($this->runs === $this->getServerBackup() * 60 * 60) {
				$backThread = new BackThread();

				$backThread->run();
			}
		}
		if($this->restartOnOverload()) {
			if($this->runs === 6000) {
				if(Math::isOverloaded($this->getMemoryLimit())) {
					$this->getTimer()->initiateRestart(Timer::OVERLOADED);
				}
			}
		}
		if(is_int($this->getBroadcastInterval())) {
			if($this->runs === $this->getBroadcastInterval()) {
				if(!$this->getTimer()->isPaused()) {
					if($this->getTimer()->getTime() >= $this->getCountdownStart()) {
						$this->getTimer()->broadcastTime($this->getMessages("broadcast"), $this->getDisplayType());
					}
				}
			}
		}
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
        return $this->getServer("Lobby");
    }

    public function getTotalMaxSlots() : int {
        foreach($this->getServers() as $server) {
            if($server instanceof Server) {
                return $this->core->getServer()->getMaxPlayers() + $server->getMaxSlots();
            }
        }
        return 0;
    }

    public function getTotalOnlinePlayers() : array {
        foreach($this->getServers() as $server) {
            if($server instanceof Server) {
                return array_merge($server->getOnlinePlayers(), $this->core->getServer()->getOnlinePlayers());
            }
        }
        return [];
    }
}