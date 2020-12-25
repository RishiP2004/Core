<?php

declare(strict_types = 1);

namespace core\anticheat;

use core\Core;

use core\utils\Manager;

use core\anticheat\cheat\{
	Cheat,
	AutoClicker
};
use core\anticheat\entity\PrimedTNT;

use pocketmine\Server;

use pocketmine\entity\{
	Animal,
	Entity,
	Human,
	Monster,
	object\ItemEntity
};

use pocketmine\level\Explosion;

class AntiCheat extends Manager implements Cheats {
	public static $instance = null;

    private $onGoing;
    private $queue;

    private $runs = 0;
	/**
	 * @var Animal[]
	 */
    public $animals = [];
	/**
	 * @var Monster[]
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

    public function init() {
    	self::$instance = $this;

		Entity::registerEntity(PrimedTNT::class, true);

        $this->onGoing = new \SplFixedArray(self::MAX_CONCURRENT_EXPLOSIONS);
        $this->queue = new \SplQueue();

        $this->initCheat(new AutoClicker());
        $this->registerCommand(\core\anticheat\command\Cheat::class, new \core\anticheat\command\Cheat($this));
        $this->registerListener(new AntiCheatListener($this), Core::getInstance());
    }

    public static function getInstance() : self {
    	return self::$instance;
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

	public function tick() : void {
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
        } else if($this->runs % mktime(self::LAG_CLEAR_TIME["hours"], self::LAG_CLEAR_TIME["minutes"]) === 0) {
            foreach(Server::getInstance()->getLevels() as $level) {
                foreach($level->getEntities() as $entity) {
                    if($entity instanceof Human) {
                        continue;
                    }
                    $entity->flagForDespawn();
                }
            }
            Server::getInstance()->broadcastMessage(Core::PREFIX . "Lag has been Cleared");
        }
    }

    public function addToExplosionQueue(Explosion $explosion) {
        $this->queue->enqueue($explosion);
    }
}