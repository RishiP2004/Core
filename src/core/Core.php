<?php

declare(strict_types = 1);

namespace core;

use core\anticheat\AntiCheat;
use core\broadcast\Broadcast;
use core\essence\Essence;
use core\essentials\Essentials;
use core\mcpe\MCPE;
use core\network\Network;
use core\social\Social;
use core\stats\Stats;
use core\vote\Vote;
use core\world\World;

use poggit\libasynql\libasynql;
use poggit\libasynql\DataConnector;

use pocketmine\plugin\PluginBase;

use pocketmine\timings\TimingsHandler;

use pocketmine\utils\TextFormat;

class Core extends PluginBase {
    public static $instance = null;

    private $database;

    private $anticheat;
    private $broadcast;
    private $essence;
    private $essentials;
    private $mcpe;
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
            $this->getServer()->getLogger()->error($this->getErrorPrefix() . "Core Database connection failed");
            $this->getServer()->shutdown();
        }
        TimingsHandler::setEnabled();

        $this->anticheat = new AntiCheat($this);
        $this->broadcast = new Broadcast($this);
        $this->essence = new Essence($this);
        $this->essentials = new Essentials($this);
        $this->mcpe = new MCPE($this);
        $this->network = new Network($this);
        $this->social = new Social($this);
        $this->stats = new Stats($this);
        $this->vote = new Vote($this);
        $this->world = new World($this);
        $this->coreTask = new CoreTask($this);

        $this->getServer()->getPluginManager()->registerEvents(new CoreListener($this), $this);
        $this->getScheduler()->scheduleRepeatingTask($this->coreTask, 1);
        $this->getServer()->getLogger()->notice($this->getPrefix() . "Core Enabled");
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

    public function getMCPE() : MCPE {
        return $this->mcpe;
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

    public function getPrefix() : string {
        return self::PREFIX;
    }

    public function getErrorPrefix() : string {
        return self::ERROR_PREFIX;
    }

    public function onDisable() {
		$this->getStats()->unloadUsers();
        $this->getServer()->getLogger()->notice($this->getPrefix() . "Core Disabled");
    }
}