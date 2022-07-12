<?php

declare(strict_types = 1);

namespace core\network;

use pocketmine\event\Listener;

use pocketmine\event\server\QueryRegenerateEvent;

class NetworkListener implements Listener {
	public function __construct(private NetworkManager $manager) {}

	public function onQueryRegenerate(QueryRegenerateEvent $event) : void {
		$event->getQueryInfo()->setPlayerCount(count($this->manager->getTotalOnlinePlayers()));
		$event->getQueryInfo()->setMaxPlayerCount($this->manager->getTotalMaxSlots());

		$players = [];

		foreach($this->manager->getTotalOnlinePlayers() as $onlinePlayer) {
			$players[] = $onlinePlayer;
		}
		/**
		foreach($this->manager->getServers() as $server) {
			$server->query();
		}*/
		$event->getQueryInfo()->setPlayerList($players);

        //$server_display_name = "ATHENA";//$this->core->getServerNameWithRandomColor();
	    //$event->getQueryInfo()->setServerName($server_display_name);
	   // Server::getInstance()->getNetwork()->setName($server_display_name);
	}
}