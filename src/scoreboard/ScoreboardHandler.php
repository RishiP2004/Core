<?php

declare(strict_types = 1);

namespace scoreboard;

use pocketmine\plugin\Plugin;

use pocketmine\event\player\PlayerQuitEvent;

use pocketmine\event\Listener;

class ScoreboardHandler implements Listener{

	/** @var Plugin|null */
	private static $registrant;

	public static function isRegistered() : bool {
		return self::$registrant instanceof Plugin;
	}

	public static function getRegistrant() : Plugin {
		return self::$registrant;
	}

	public static function register(Plugin $plugin) : void {
		if(self::isRegistered()){
			throw new \Error($plugin->getName() . "attempted to register " . self::class . " twice.");
		}
		self::$registrant = $plugin;
		$plugin->getServer()->getPluginManager()->registerEvents(new ScoreboardHandler(), $plugin);
	}

	private function __construct(){
	}

	public function onQuit(PlayerQuitEvent $event) : void{
		ScoreboardManager::removePotentialViewer($event->getPlayer()->getName());
	}
}