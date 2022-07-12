<?php

declare(strict_types = 1);

namespace core;

use core\anticheat\AntiCheatManager;
use core\broadcast\BroadcastManager;
use core\database\Database;
use core\essence\EssenceManager;
use core\essential\EssentialManager;
use core\network\NetworkManager;
use core\player\PlayerManager;
use core\social\SocialManager;
use core\vote\VoteManager;
use core\world\WorldManager;
use core\scheduler\CoreTask;

use muqsit\invmenu\InvMenuHandler;
use scoreboard\ScoreboardHandler;
use CortexPE\Commando\PacketHooker;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\TextFormat;

class Core extends PluginBase {
    public static ?Core $instance = null;

    private CoreTask $coreTask;

    const PREFIX = TextFormat::BLUE . "Athena> " . TextFormat::GRAY;
    const ERROR_PREFIX = TextFormat::DARK_RED . "Error> " . TextFormat::GRAY;

    public function onLoad() : void {
        self::$instance = $this;
    }

    public function onEnable() : void {
        if(!is_dir($concurrentDirectory = $this->getDataFolder()) && !mkdir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
        if(!is_dir($concurrentDirectory = $this->getDataFolder() . DIRECTORY_SEPARATOR . "mysql") && !mkdir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
        $this->saveDefaultConfig();

		if(!InvMenuHandler::isRegistered()) {
			InvMenuHandler::register($this);
		}
		if(!ScoreboardHandler::isRegistered()) {
			ScoreboardHandler::register($this);
		}
		if(!PacketHooker::isRegistered()) {
			PacketHooker::register($this);
		}
		$this->initManagers();

        $this->coreTask = new CoreTask($this);

        $this->getScheduler()->scheduleRepeatingTask($this->coreTask, 1);
        $this->getServer()->getLogger()->notice(self::PREFIX . "Core Enabled");
    }

	public function initManagers() {
		Database::initialize();
		new AntiCheatManager();
		new BroadcastManager();
		new EssenceManager();
		new EssentialManager();
		new NetworkManager();
		new SocialManager();
		new PlayerManager();
		new VoteManager();
		new WorldManager();
	}

    public static function getInstance() : Core {
        return self::$instance;
    }

    public function getCoreTask() :  CoreTask {
        return $this->coreTask;
    }

    public function onDisable() : void {
		if(PlayerManager::getInstance() !== null) {
			PlayerManager::getInstance()->unloadUsers();
		}
		Database::get()->waitAll();
		Database::get()->close();
		$this->getScheduler()->cancelAllTasks();
        $this->getServer()->getLogger()->notice(self::PREFIX. "Core Disabled");
    }
}