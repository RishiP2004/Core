<?php

declare(strict_types = 1);

namespace core\essence;

use core\Core;
use core\CorePlayer;

use core\essence\floatingText\{
    FloatingText,
    LobbyGreetings,
    Parkour,
	TopVoter,
	TopVoter2
};

use core\essence\npc\{
    NPC,
    Athie,
    Factions,
    Lobby
};

class Essence implements EssenceData {
    private $core;

    private $NPCs = [], $floatingTexts = [];

    private $runs = 0;

    public function __construct(Core $core) {
        $this->core = $core;

        $this->initFloatingText(new LobbyGreetings());
        $this->initFloatingText(new Parkour());
		$this->initFloatingText(new TopVoter());
		//$this->initFloatingText(new TopVoter2());
        $this->initNPC(new Athie());
        $this->initNPC(new Factions());
        $this->initNPC(new Lobby());
		$core->getServer()->getPluginManager()->registerEvents(new EssenceListener($core), $core);
    }

	public function tick() : void {
		$this->runs++;

		foreach($this->getFloatingTexts() as $floatingText) {
			if(!is_null($floatingText->getUpdateTime())) {
				if($this->runs * $floatingText->getUpdateTime() === 0) {
					$floatingText->update();
				}
			}
		}
		foreach($this->getNPCs() as $npc) {	
			foreach($this->core->getServer()->getOnlinePlayers() as $onlinePlayer) {
				if($onlinePlayer instanceof CorePlayer) {
					if($npc->isSpawnedTo($onlinePlayer)) {
						if($npc->getMoveTime() !== 0) {
							if($this->runs % 20 === $npc->getMoveTime()) {
								$npc->move($onlinePlayer);
							}
						}
					}
				}
			}
		}	
	}

    public function initFloatingText(FloatingText $floatingText) {
        $this->floatingTexts[$floatingText->getName()] = $floatingText;
    }
    /**
     * @return FloatingText[]
     */
    public function getFloatingTexts() : array {
        return $this->floatingTexts;
    }

    public function getFloatingText(string $floatingText) : ?FloatingText {
        $lowerKeys = array_change_key_case($this->floatingTexts, CASE_LOWER);

        if(isset($lowerKeys[strtolower($floatingText)])) {
            return $lowerKeys[strtolower($floatingText)];
        }
        return null;
    }

    public function initNPC(NPC $NPC) {
        $this->NPCs[$NPC->getName()] = $NPC;
    }
    /**
     * @return NPC[]
     */
    public function getNPCs() : array {
        return $this->NPCs;
    }

    public function getNPC(string $NPC) : ?NPC {
        $lowerKeys = array_change_key_case($this->NPCs, CASE_LOWER);

        if(isset($lowerKeys[strtolower($NPC)])) {
            return $lowerKeys[strtolower($NPC)];
        }
        return null;
    }
}