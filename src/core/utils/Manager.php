<?php

namespace core\utils;

use core\Core;
use core\player\traits\PlayerCallTrait;

use CortexPE\Commando\BaseCommand;

use pocketmine\Server;

use pocketmine\plugin\Plugin;

use pocketmine\event\Listener;

use pocketmine\scheduler\{
	Task,
	AsyncTask
};

use pocketmine\plugin\PluginDescription;

use pocketmine\permission\{
	DefaultPermissions,
	PermissionManager,
	PermissionParser
};

abstract class Manager {
	use PlayerCallTrait;

	protected Core $core;

	final public function __construct() {
		$this->init();
	}

	public abstract static function getInstance() : self;
	
	public abstract function init() : void;

	final protected function registerCommands($className, BaseCommand ...$command) : void {
		Server::getInstance()->getCommandMap()->registerAll($className, $command);
	}

	final protected function registerListener(Listener $listener, Plugin $core) : void {
		Server::getInstance()->getPluginManager()->registerEvents(new $listener($this), $core);
	}

	final protected function registerRepeatingTask(Task $task, int $period) : void {
		Core::getInstance()->getScheduler()->scheduleRepeatingTask($task, $period);
	}

	final protected function registerAsyncTank(AsyncTask $task) : void {
		Core::getInstance()->getServer()->getAsyncPool()->submitTask(new $task);
	}
	
	final protected function registerPermissions(array $permissions) : void {
		$refClass = new \ReflectionClass(PluginDescription::class);
		$refProp = $refClass->getProperty("permissions");
		$refProp->setAccessible(true);

		$permissions = PermissionParser::loadPermissions($permissions);

		$desc = Core::getInstance()->getDescription();
		$pluginPerms = $refProp->getValue($desc);
		$permManager = PermissionManager::getInstance();

		$opROOT = $permManager->getPermission(DefaultPermissions::ROOT_OPERATOR);
		$evROOT = $permManager->getPermission(DefaultPermissions::ROOT_OPERATOR);

		foreach($permissions as $default => $_permissions) {
			foreach($_permissions as $permission) {
				switch($default){
					case PermissionParser::DEFAULT_OP:
						$opROOT->addChild($permission->getName(), true);
						break;
					case PermissionParser::DEFAULT_NOT_OP:
						$evROOT->addChild($permission->getName(), true);
						$opROOT->addChild($permission->getName(), false);
						break;
					case PermissionParser::DEFAULT_TRUE:
						$evROOT->addChild($permission->getName(), true);
						break;
				}
				$pluginPerms[$default][] = $permission;
				$permManager->addPermission($permission);
			}
		}
		$refProp->setValue($desc, $pluginPerms);
	}
}