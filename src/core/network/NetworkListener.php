<?php

declare(strict_types = 1);

namespace core\network;

use core\Core;

use pocketmine\event\Listener;

use pocketmine\event\server\QueryRegenerateEvent;

class NetworkListener implements Listener {
	private $core;

	public function __construct(Core $core) {
		$this->core = $core;
	}

	public function onQueryRegenerate(QueryRegenerateEvent $event) {
		$event->setPlayerCount(count($this->core->getNetwork()->getTotalOnlinePlayers()));
		$event->setMaxPlayerCount($this->core->getNetwork()->getTotalMaxSlots());

		$players = [];

		foreach($this->core->getNetwork()->getTotalOnlinePlayers() as $onlinePlayer) {
			$players[] = $onlinePlayer;
		}
		foreach($this->core->getNetwork()->getServers() as $server) {
			$server->query();
		}
		$event->setPlayerList($players);
	}
}