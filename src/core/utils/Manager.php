<?php

namespace core\utils;

use core\Core;

use pocketmine\Server;

use pocketmine\plugin\Plugin;

use pocketmine\command\PluginCommand;

use pocketmine\event\Listener;

use pocketmine\scheduler\{
	Task,
	AsyncTask
};

abstract class Manager {
	protected $core;

	public function __construct() {
		$this->init();
	}

	public abstract static function getInstance();
	
	public abstract function init();

	public function registerCommand($cmd, PluginCommand $command) {
		Server::getInstance()->getCommandMap()->register($cmd, $command);
	}

	public function registerListener(Listener $listener, Plugin $core) {
		Server::getInstance()->getPluginManager()->registerEvents(new $listener($this), $core);
	}

	public function registerRepeatingTask(Task $task, int $period) {
		Core::getInstance()->getScheduler()->scheduleRepeatingTask($task, $period);
	}

	public function registerAsyncTank(AsyncTask $task) {
		Core::getInstance()->getServer()->getAsyncPool()->submitTask(new $task);
	}
}