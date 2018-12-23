<?php

namespace core\stats\task;

use core\Core;
use core\CorePlayer;

use pocketmine\scheduler\Task;

class AFKSetterTask extends Task {
	private $core;

	public function __construct(Core $core) {
		$this->core = $core;
	}
	
	public function onRun(int $currentTick) {
		foreach($this->core->getServer()->getOnlinePlayers() as $onlinePlayer) {
			if($onlinePlayer instanceof CorePlayer) {
				if(!$onlinePlayer->isAFK() && ($last = $onlinePlayer->getLastMovement()) !== null && !$onlinePlayer->hasPermission("GPCore.Stats.AFK.PreventAuto")) {
					if(time() - $last >= $this->core->getStats()->getAFKAutoSet()) {
						$onlinePlayer->setAFK(true);
					}
				}
			}
		}
		$this->core->getStats()->scheduleAFKSetter();
	}
}