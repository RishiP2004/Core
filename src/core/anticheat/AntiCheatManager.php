<?php

declare(strict_types = 1);

namespace core\anticheat;

use core\Core;

use core\utils\Manager;

use core\player\CorePlayer;

use core\anticheat\cheat\{
	Cheat,
	AutoClicker,
	AntiGlitch
};
use core\anticheat\command\CheatCommand;

use core\anticheat\entity\PrimedTNT;

use pocketmine\Server;

use pocketmine\entity\{
	Entity,
	EntityDataHelper,
	EntityFactory,
	Human,
	object\ItemEntity
};

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\world\World;

use pocketmine\world\Explosion;
//TODO: USE SENDUSAGE ON PARENT CMDS?
class AntiCheatManager extends Manager implements Cheats {
	public static ?self $instance = null;

    private \SplFixedArray $onGoing;
    private \SplQueue $queue;

    private int $runs = 0;
	//Wait for PMMP to re-implement?
	/*
    public $animals = [];
    public $monsters = [];
    */
	/** @var Entity[] */
    public array $entities = [];
	/**
	 * @var ItemEntity[]
	 */
    public array $itemEntities = [];
	/**
	 * @var string[]
	 */
    public array $ids = [];
	/**
	 * @var Cheat[]
	 */
    public array $cheats = [];

	public array $analyzers = [];

	public function getName() : string {
		return "Anti Cheat";
	}

    public function init() : void {
    	self::$instance = $this;

    	EntityFactory::getInstance()->register(PrimedTNT::class, function (World $world, CompoundTag $nbt) : PrimedTNT {
			return new PrimedTNT(EntityDataHelper::parseLocation($nbt, $world));
		}, ["PrimtedTNT"]);

        $this->onGoing = new \SplFixedArray(self::MAX_CONCURRENT_EXPLOSIONS);
        $this->queue = new \SplQueue();

        $this->initCheat(new AutoClicker());
		$this->initCheat(new AntiGlitch());
		$this->registerPermissions([
			"cheat.command" => [
				"default" => "op",
				"description" => "Anti Cheat command"
			],
			"cheat.subcommand.help" => [
				"default" => "op",
				"description" => "See available Anti Cheat commands"
			],
			"cheat.subcommand.report" => [
				"default" => "true",
				"description" => "Report a player"
			],
			"cheat.subcommand.history" => [
				"default" => "op",
				"description" => "See Cheat history of a player"
			],
		]);
        $this->registerCommands("anticheat", new CheatCommand(Core::getInstance(), "cheat", "AntiCheat Command"));
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
            foreach(Server::getInstance()->getWorldManager()->getWorlds() as $world) {
                foreach($world->getEntities() as $entity) {
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

    public function setWatch(CorePlayer $player) {
		foreach(AntiCheatManager::getInstance()->getCheats() as $cheat) {
			$cheat->set($player);
		}
	}
}