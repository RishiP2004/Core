<?php

declare(strict_types = 1);

namespace core\essence\task;

use core\Core;
use core\CorePlayer;

use pocketmine\scheduler\Task;

class MoveNPC extends Task {
    private $core;
	
    public function __construct(Core $core) {
		$this->core = $core;
    }
	
    public function onRun(int $currentTick) {
		foreach($this->core->getServer()->getOnlinePlayers() as $onlinePlayer) {
			if($onlinePlayer instanceof CorePlayer) {
				$onlinePlayer->moveNPCs();
			}
		}
    }
}