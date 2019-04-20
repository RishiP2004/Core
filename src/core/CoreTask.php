<?php

namespace core;

use pocketmine\scheduler\Task;

class CoreTask extends Task {
    private $core;

    private $runs = 0;

    public function __construct(Core $core) {
        $this->core = $core;
    }

    public function onRun(int $currentTick) {
        $this->runs++;

        if($this->runs % 20 === 0) {
            $this->core->getAntiCheat()->tick();
            $this->core->getBroadcast()->tick();
        }
		if($this->runs % 1 === 0) {
			$this->core->getMCPE()->tick();
		}
		if($this->runs * 20 * 60) {
			$this->core->getNetwork()->tick();
		}
    }
}