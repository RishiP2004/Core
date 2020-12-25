<?php

declare(strict_types = 1);

namespace core;

use core\anticheat\AntiCheat;
use core\broadcast\Broadcast;
use core\essence\Essence;
use core\essentials\Essentials;
use core\network\Network;
use core\social\Social;
use core\stats\Stats;
use core\vote\Vote;
use core\world\World;

use poggit\libasynql\{
	libasynql,
	DataConnector
};
use muqsit\invmenu\InvMenuHandler;
use scoreboard\ScoreboardHandler;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\TextFormat;

//VIRIONS REQUIRED: apibossbar, DiscordWebHookAPI, Form, InvMenu, libasynql, Scoreboard, Twitter 

class Core extends PluginBase {
    public static $instance = null;

    private $database;

    private $anticheat;
    private $broadcast;
    private $essence;
    private $essentials;
    private $network;
    private $social;
    private $stats;
    private $vote;
    private $world;

    private $coreTask;

    const PREFIX = TextFormat::BLUE . "Athena> " . TextFormat::GRAY;
    const ERROR_PREFIX = TextFormat::DARK_RED . "Error> " . TextFormat::GRAY;

    public function onLoad() {
        self::$instance = $this;
    }

    public function onEnable() {
        if(!is_dir($concurrentDirectory = $this->getDataFolder()) && !mkdir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
		if(!is_dir($concurrentDirectory = $this->getDataFolder() . DIRECTORY_SEPARATOR . "mcpe") && !mkdir($concurrentDirectory)) {
			throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
		}
        if(!is_dir($concurrentDirectory = $this->getDataFolder() . DIRECTORY_SEPARATOR . "mysql") && !mkdir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
        $this->saveDefaultConfig();
        $this->saveResource("mysql/queries.sql");

        try {
            $this->database = libasynql::create($this, $this->getConfig()->get("database"), [
                "mysql" => "mysql/queries.sql"
            ]);
        } catch(\Exception $exception) {
            $this->getServer()->getLogger()->error(self::ERROR_PREFIX . "Core Database connection failed: " . $exception->getMessage());
            $this->getServer()->shutdown();
        }
		if(!InvMenuHandler::isRegistered()) {
			InvMenuHandler::register($this);
		}
		if(!ScoreboardHandler::isRegistered()) {
			ScoreboardHandler::register($this);
		}
        $this->anticheat = new AntiCheat();
        $this->broadcast = new Broadcast();
        $this->essence = new Essence();
        $this->essentials = new Essentials();
        $this->network = new Network();
        $this->social = new Social();
        $this->stats = new Stats();
        $this->vote = new Vote();
        $this->world = new World();
        $this->coreTask = new CoreTask($this);

        $this->getServer()->getPluginManager()->registerEvents(new CoreListener($this), $this);
        $this->getScheduler()->scheduleRepeatingTask($this->coreTask, 1);
        $this->getServer()->getLogger()->notice(self::PREFIX . "Core Enabled");
    }

    public static function getInstance() : Core {
        return self::$instance;
    }

    public function getDatabase() : DataConnector {
        return $this->database;
    }

    public function getAntiCheat() : AntiCheat {
        return $this->anticheat;
    }

    public function getBroadcast() : Broadcast {
        return $this->broadcast;
    }

    public function getEssence() : Essence {
        return $this->essence;
    }

    public function getEssentials() : Essentials {
        return $this->essentials;
    }

    public function getNetwork() : Network {
        return $this->network;
    }

    public function getSocial() : Social {
        return $this->social;
    }

    public function getStats() : Stats {
        return $this->stats;
    }

    public function getVote() : Vote {
        return $this->vote;
    }

    public function getWorld() : World {
        return $this->world;
    }

    public function getCoreTask() :  CoreTask {
        return $this->coreTask;
    }

    public function onDisable() {
		if($this->stats !== null) {
			$this->getStats()->unloadUsers();
		}
		$this->getScheduler()->cancelAllTasks();
        $this->getServer()->getLogger()->notice(self::PREFIX. "Core Disabled");
    }
}