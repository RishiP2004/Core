<?php

declare(strict_types = 1);

namespace core\anticheat;

use core\anticheat\cheat\AutoClicker;
use core\Core;

use core\anticheat\entity\PrimedTNT;

use core\anticheat\cheat\Cheat;

use core\mcpe\entity\{
	AnimalBase,
	MonsterBase,
};
use core\mcpe\entity\object\ItemEntity;

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
	/**
	 * @var AnimalBase[]
	 */
    public $animals = [];
	/**
	 * @var MonsterBase[]
	 */
    public $monsters = [];
	/**
	 * @var ItemEntity[]
	 */
    public $itemEntities = [];
	/**
	 * @var string[]
	 */
    public $ids = [];

    public $cheats = [], $analyzers = [];

    public function __construct(Core $core) {
		$this->core = $core;
		
		Entity::registerEntity(PrimedTNT::class, true);

        $this->onGoing = new \SplFixedArray($this->getMaxConcurrentExplosions());
        $this->queue = new \SplQueue();

        $this->initCheat(new AutoClicker());
    }
	
	public function getMaxConcurrentExplosions() : int {
		return self::MAX_CONCURRENT_EXPLOSIONS;
	}

    public function getAutoClickAmount() : int {
        return self::AUTO_CLICK_AMOUNT;
    }

	public function getLagClearTime(string $key) : int {
		return self::LAG_CLEAR_TIME[$key];
	}

	public function getMaxEntities(string $key) : int {
    	return self::MAX_ENTITIES[$key];
	}

	public function initCheat(Cheat $cheat) {
		$this->cheats[$cheat->getId()] = $cheat;
	}
	/**
	 * @return Cheat[]
	 */
	public function getCheats() : array {
		return $this->cheats;
	}

	public function getCheat(string $cheat) : ?Cheat {
		$lowerKeys = array_change_key_case($this->cheats, CASE_LOWER);

		if(isset($lowerKeys[strtolower($cheat)])) {
			return $lowerKeys[strtolower($cheat)];
		}
		return null;
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
                    $entity->flagForDespawn();
                }
            }
            $this->core->getServer()->broadcastMessage($this->core->getPrefix() . "Lag has been Cleared");
        }
    }

    public function addToExplosionQueue(Explosion $explosion) {
        $this->queue->enqueue($explosion);
    }
}