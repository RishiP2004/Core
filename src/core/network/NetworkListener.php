<?php

declare(strict_types = 1);

namespace core\network;

use pocketmine\event\Listener;

use pocketmine\event\server\QueryRegenerateEvent;

class NetworkListener implements Listener {
	private $manager;

	public function __construct(Network $manager) {
		$this->manager = $manager;
	}

	public function onQueryRegenerate(QueryRegenerateEvent $event) {
		$event->setPlayerCount(count($this->manager->getTotalOnlinePlayers()));
		$event->setMaxPlayerCount($this->manager->getTotalMaxSlots());

		$players = [];

		foreach($this->manager->getTotalOnlinePlayers() as $onlinePlayer) {
			$players[] = $onlinePlayer;
		}
		foreach($this->manager->getServers() as $server) {
			//$server->query();
		}
		$event->setPlayerList($players);
	}
}