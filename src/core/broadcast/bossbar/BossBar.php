<?php

declare(strict_types = 1);

namespace core\broadcast\bossbar;

use xenialdan\apibossbar\BossBar as APIBossBar;

use core\Core;

use core\player\CorePlayer;

use pocketmine\Server;

use pocketmine\world\World;

class BossBar implements Messages {
    public APIBossBar $bossBar;

    private int $run = 0;

    public int $int = 0;

    public function __construct() {
        $this->bossBar = new APIBossBar();
    }

    public function get() : APIBossBar {
    	return $this->bossBar;
	}

    public function tick() : void {
    	$this->run++;

        if(is_int(self::CHANGING["time"])) {
            if($this->run === self::CHANGING["time"] * 20) {
				$worlds = $this->getWorlds();

				foreach($worlds as $world) {
					if($world instanceof World) {
						foreach($world->getPlayers() as $player) {
							if($player instanceof CorePlayer) {
								$player->setBarText();
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
        $worldNames = self::WORLDS;
        $worlds = [];

        switch($mode) {
            case 0:
                $worlds = Server::getInstance()->getWorldManager()->getWorlds();
            break;
            case 1:
                foreach($worldNames as $name) {
                    if(is_null($level = Server::getInstance()->getWorldManager()->getWorldByName($name))) {
                        Server::getInstance()->getLogger()->error(Core::PREFIX . "World provided in BossBar does not exist");
                    } else {
                        $worlds[] = $level;
                    }
                }
            break;
            case 2:
                $worlds = Server::getInstance()->getWorldManager()->getWorlds();

                foreach($worlds as $world) {
                    if($world instanceof World) {
                        if(!in_array(strtolower($world->getFolderName()), $worldNames)) {
                            $worlds[] = $world;
                        }
                    }
                }
            break;
        }
        return $worlds;
    }
}
