<?php

declare(strict_types = 1);

namespace core\stats\task;

use core\Core;
use core\CorePlayer;

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
		$this->player->getLevel()->broadcastLevelEvent($this->player->asPosition(), LevelEventPacket::EVENT_GUARDIAN_CURSE);
				
		switch($this->core->getNetwork()->getServerFromIp($this->player->getServer()->getIp())->getName()) {
			case "Lobby":
				$this->player->addTitle($this->core->getPrefix(), TextFormat::GRAY . "Lobby");
				$this->player->sendMessage(Lobby::getInstance()->getPrefix() . "Welcome to the GratonePix Lobby!");
			break;
			case "Factions":
				$this->player->addTitle($this->core->getPrefix(), TextFormat::RED . "Factions");
				$this->player->sendMessage(Factions::getInstance()->getPrefix() . "Welcome to GratonePix Factions!");
			break;
		}
    }
}