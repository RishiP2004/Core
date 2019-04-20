<?php

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

use pocketmine\scheduler\Task;

class Network implements Networking {
    private $core;

    private $timer;

    public $servers = [];

    const CHAT = 0;
    const POPUP = 1;
    const TITLE = 2;

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

    public function getCountdownStart() : int {
        return self::COUNTDOWN_START;
    }

    public function getInterval() : int {
        return self::INTERVAL;
    }

    public function getMemoryLimit() : string {
        return self::MEMORY_LIMIT;
    }

    public function getCountdownType() : string {
        return self::COUNTDOWN_TYPE;
    }

    public function restartOnOverload() : bool {
        return self::RESTART_ON_OVERLOAD;
    }

    public function getMessages() : array {
        return self::MESSAGES;
    }

    public function tick() {
    	if(is_int(self::SERVER_BACKUP)) {
    		$this->core->getScheduler()->scheduleRepeatingTask(new class extends Task {
    			public function onRun(int $currentTick) {
					$backThread = new BackThread;

					$backThread->run();
				}
			}, self::SERVER_BACKUP)	;
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

    public function getServer(string $area) : ?Server {
        $lowerKeys = array_change_key_case($this->servers, CASE_LOWER);

        if(isset($lowerKeys[strtolower($area)])) {
            return $lowerKeys[strtolower($area)];
        }
        return null;
    }

    public function getServerFromIp(string $ip) : Server {
        foreach($this->getServers() as $server) {
            if($server->getIp() === $ip) {
                return $this->getServer($server);
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