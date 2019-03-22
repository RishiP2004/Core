<?php

namespace core\stats\task;

use core\Core;
use core\CorePlayer;

use pocketmine\scheduler\Task;

class AFKKick extends Task {
    private $core;

    private $player;

    public function __construct(Core $core, CorePlayer $player) {
        $this->core = $core;
        $this->player = $player;
    }

    public function onRun(int $currentTick) {
        if($this->player instanceof CorePlayer && $this->player->isOnline() && $this->player->isAFK() && !$this->player->hasPermission("core.stats.afk.kickexempt") && time() - $this->player->getLastMovement() >= $this->core->getStats()->getAFKAutoSet()) {
            $this->player->kick($this->core->getPrefix() . "You have been kicked for idling more than " . (($time = floor($this->core->getStats()->getAFKAutoKick())) / 60 >= 1 ? ($time / 60) . " minutes" : $time . " seconds"), false);
        }
    }
}