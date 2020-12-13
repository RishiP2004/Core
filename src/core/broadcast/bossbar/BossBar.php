<?php

declare(strict_types = 1);

namespace core\broadcast\bossbar;

use core\Core;
use core\CorePlayer;

use pocketmine\level\Level;

class BossBar implements Messages {
    private $core;

    public $bossBar;

    private $run = 0;

    public $int = 0;

    public function __construct(Core $core) {
        $this->core = $core;
        $this->bossBar = new \xenialdan\apibossbar\BossBar();
    }

    public function get() {
    	return $this->bossBar;
	}

    public function tick() : void {
    	$this->run++;

        if(is_int(self::CHANGING["time"])) {
            if($this->run === self::CHANGING["time"] * 20) {
				$worlds = $this->getWorlds();

				foreach($worlds as $world) {
					if($world instanceof Level) {
						foreach($world->getPlayers() as $player) {
							if($player instanceof CorePlayer) {
								$player->setText();
								$this->int++;
							}
						}
					}
				}
            }
        }
    }

    public function getWorlds() : ?array {
        $mode = self::MODE;
        $worldNames = $this->getWorlds();
        $worlds = [];

        switch($mode) {
            case 0:
                $worlds = $this->core->getServer()->getLevels();
            break;
            case 1:
                foreach($worldNames as $name) {
                    if(is_null($level = $this->core->getServer()->getLevelByName($name))) {
                        $this->core->getServer()->getLogger()->error($this->core->getPrefix() . "World provided in BossBar config does not exist");
                    } else {
                        $worlds[] = $level;
                    }
                }
            break;
            case 2:
                $worlds = $this->core->getServer()->getLevels();

                foreach($worlds as $world) {
                    if($world instanceof Level) {
                        if(!in_array(strtolower($world->getName()), $worldNames)) {
                            $worlds[] = $world;
                        }
                    }
                }
            break;
        }
        return $worlds;
    }
}
