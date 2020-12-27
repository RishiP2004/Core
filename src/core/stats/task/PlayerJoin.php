<?php

declare(strict_types = 1);

namespace core\stats\task;

use core\Core;
use core\CorePlayer;
use core\network\Network;
use lobby\Lobby;
use factions\Factions;

use pocketmine\scheduler\Task;

use pocketmine\utils\TextFormat;

use pocketmine\network\mcpe\protocol\LevelEventPacket;

class PlayerJoin extends Task {
    private $core;
    
    private $player;
    
    public function __construct(Core $core, CorePlayer $player) {
        $this->core = $core;
        
        $this->player = $player;
    }
    
    public function onRun(int $currentTick) {
		if(!$this->player->isOnline()) {
			return;
		}
		$this->player->getLevel()->broadcastLevelEvent($this->player->asPosition(), LevelEventPacket::EVENT_GUARDIAN_CURSE);
				
		switch(Network::getInstance()->getServerFromIp($this->player->getServer()->getIp())->getName()) {
			case "Lobby":
				$this->player->addTitle($this->core::PREFIX, TextFormat::GRAY . "Lobby");
				$this->player->sendMessage(Lobby::PREFIX . "Welcome to the GratonePix Lobby!");
			break;
			case "Survival":
				$this->player->addTitle($this->core::PREFIX, TextFormat::RED . "Survival");
				$this->player->sendMessage(Factions::PREFIX . "Welcome to GratonePix Survival!");
			break;
		}
    }
}