<?php

declare(strict_types = 1);

namespace core\network;

use core\Core;

use core\database\Database;

use core\player\PlayerManager;

use core\utils\{
	Manager,
	MathUtils
};

use core\network\server\Server;
use core\network\command\RestarterCommand;

use lobby\registry\server\Lobby;

use pocketmine\utils\TextFormat;

//REWRITE WITH PROXIES, SOCKETS. ALSO TIMER SHOULD BE FOR EACH SERVER.
class NetworkManager extends Manager implements Networking {
	public $socket;

	public static ?self $instance = null;

    private Timer $timer;

    public array $servers = [];

    const CHAT = 0;
    const POPUP = 1;
    const TITLE = 2;

    private int $runs = 0;

    public function init() : void {
		//$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		//socket_connect($this->socket, \pocketmine\Server::getInstance()->getIp(), 19132);
		/**
		if (PracticeCore::PROXY) {
			$this->getServer()->getPluginManager()->registerEvent(NetworkInterfaceRegisterEvent::class, function (NetworkInterfaceRegisterEvent $event): void {
				$network = $event->getInterface();
				if ($network instanceof DedicatedQueryNetworkInterface) {
					$event->cancel();
					return;
				}
				if ($network instanceof RakLibInterface && !$network instanceof WDPERakLibInterface) {
					$event->cancel();
					$this->getServer()->getNetwork()->registerInterface(new WDPERakLibInterface($this->getServer(), $this->getServer()->getIp(), $this->getServer()->getPort(), false));
					if ($this->getServer()->getConfigGroup()->getConfigBool("enable-ipv6", true)) {
						$this->getServer()->getNetwork()->registerInterface(new WDPERakLibInterface($this->getServer(), $this->getServer()->getIpV6(), $this->getServer()->getPort(), true));
					}
				}
			}, EventPriority::NORMAL, $this, true);

			new ProxyListener();
			new ProxyTask();
		}*/
		
    	self::$instance = $this;
        $this->timer = new Timer();

		$this->registerPermissions([
			"restarter.command" => [
				"default" => "op",
				"description" => "Restarter command"
			],
			"restarter.subcommand.add" => [
				"default" => "op",
				"description" => "Add to restart timer"
			],
			"restarter.subcommand.help" => [
				"default" => "op",
				"description" => "See available Restarter commands"
			],
			"restarter.subcommand.memory" => [
				"default" => "op",
				"description" => "Check server memory"
			],
			"restarter.subcommand.set" => [
				"default" => "op",
				"description" => "Set restart timer"
			],
			"restarter.subcommand.start" => [
				"default" => "op",
				"description" => "Start restart timer"
			],
			"restarter.subcommand.stop" => [
				"default" => "op",
				"description" => "Stop restart timer"
			],
			"restarter.subcommand.subtract" => [
				"default" => "op",
				"description" => "Subtract from restart timer"
			],
			"restarter.subcommand.time" => [
				"default" => "op",
				"description" => "Check time for server restart"
			],
		]);
        $this->registerCommands("network", new RestarterCommand(Core::getInstance(), "restarter", "Restarter Command"));
        $this->registerListener(new NetworkListener($this), Core::getInstance());
    }

    public static function getInstance() : self {
		return self::$instance;
	}

	public function getTimer() : Timer {
        return $this->timer;
    }

    //SHOULD BE PER SERVER
	public function restartServer() : void {
		$startFileName = "start.sh"; //CHANGE ON VPS

		if(!file_exists($startFileName)) {
			return;
		}
		register_shutdown_function(
			static function() use ($startFileName) : void{
				pcntl_exec("./$startFileName");
			}
		);
		if(PlayerManager::getInstance() !== null) {
			PlayerManager::getInstance()->unloadUsers();
		}
		foreach(\pocketmine\Server::getInstance()->getOnlinePlayers() as $player){
			$player->kick(TextFormat::BOLD . TextFormat::RED . "Network Restart");
		}
		Database::get()->waitAll();
		Database::get()->waitAll();
		\pocketmine\Server::getInstance()->shutdown();
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
		if(self::RESTART_ON_OVERLOAD) {
			if($this->runs === 6000) {
				if(MathUtils::isOverloaded(self::MEMORY_LIMIT)) {
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

    public function initServer(Server $server) : void {
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