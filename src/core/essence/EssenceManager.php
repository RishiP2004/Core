<?php

declare(strict_types = 1);

namespace core\essence;

use core\Core;

use core\player\CorePlayer;

use core\utils\Manager;

use core\essence\hologram\Hologram;
use core\essence\npc\NPC;

use pocketmine\Server;

class EssenceManager extends Manager implements EssenceData {
    public static ?self $instance = null;
	/**
	 * @var NPC[]
	 */
    private array $NPCs = [];
	/**
	 * @var Hologram[]
	 */
	private array $holograms = [];

    private int $runs = 0;

    public function init() : void {
    	self::$instance = $this;

		$this->registerListener(new EssenceListener($this), Core::getInstance());
    }

	public static function getInstance() : self {
    	return self::$instance;
	}

	public function tick() : void {
		$this->runs++;

		foreach($this->getHolograms() as $floatingText) {
			if(!is_null($floatingText->getUpdateTime())) {
				if($this->runs * $floatingText->getUpdateTime() === 0) {
					$floatingText->update();
				}
			}
		}
		foreach($this->getNPCs() as $npc) {	
			foreach(Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
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

    public function initHologram(Hologram $hologram) {
        $this->holograms[$hologram->getName()] = $hologram;
    }
    /**
     * @return Hologram[]
     */
    public function getHolograms() : array {
        return $this->holograms;
    }

    public function getHologram(string $hologram) : ?Hologram {
        $lowerKeys = array_change_key_case($this->holograms, CASE_LOWER);

        if(isset($lowerKeys[strtolower($hologram)])) {
            return $lowerKeys[strtolower($hologram)];
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

    public function spawnNPCs(CorePlayer $player) : void {
		foreach($this->getNPCs() as $NPC) {
			if($NPC instanceof NPC) {
				$NPC->spawnTo($player);
			}
		}
	}

	public function despawnNPCs(CorePlayer $player) : void {
		foreach($this->getNPCs() as $NPC) {
			if($NPC instanceof NPC) {
				$NPC->despawnFrom($player);
			}
		}
	}

	public function spawnHolograms(CorePlayer $player) : void {
		foreach($this->getHolograms() as $hologram) {
			if($hologram instanceof Hologram) {
				$hologram->spawnTo($player);
			}
    	}
	}
}