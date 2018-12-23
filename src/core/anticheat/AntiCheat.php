<?php

namespace core\anticheat;

use core\Core;

use core\anticheat\entity\PrimedTNT;

use pocketmine\entity\{
    Entity,
    Human
};

use pocketmine\level\Explosion;

class AntiCheat implements Cheats {
    private $core;

    private $onGoing;
    private $queue;

    private $runs = 0;

    public function __construct(Core $core) {
		$this->core = $core;
		
		Entity::registerEntity(PrimedTNT::class, true);

        $this->onGoing = new \SplFixedArray($this->getMaxConcurrentExplosions());
        $this->queue = new \SplQueue();
    }
	
	public function getMaxConcurrentExplosions() : int {
		return self::MAX_CONCURRENT_EXPLOSIONS;
	}

    public function getAutoClickAmount() : int {
        return self::AUTO_CLICK_AMOUNT;
    }

    public function getProxyURL() : string {
        return self::PROXY_URL;
    }

    public function getProxyKey() : string {
        return self::PROXY_KEY;
    }

	public function getLagClearTime(string $key) : int {
		return self::LAG_CLEAR_TIME[$key];
	}

	public function tick() {
        $this->runs++;

        if($this->runs % 1 === 0) {
            if($this->queue->isEmpty()) {
                return;
            }
            $explosion = $this->queue->pop();

            if($explosion !== null) {
                $explosion->explodeA();
                $explosion->explodeB();
            }
        } else if($this->runs % mktime($this->core->getAntiCheat()->getLagClearTime("hours"), $this->core->getAntiCheat()->getLagClearTime("minutes")) === 0) {
            foreach($this->core->getServer()->getLevels() as $level) {
                foreach($level->getEntities() as $entity) {
                    if($entity instanceof Human) {
                        continue;
                    }
                    $entity->close();
                }
            }
            $this->core->getServer()->broadcastMessage($this->core->getPrefix() . "Lag has been Cleared");
        }
    }

    public function addToExplosionQueue(Explosion $explosion) {
        $this->queue->enqueue($explosion);
    }
}