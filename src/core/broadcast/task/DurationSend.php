<?php

declare(strict_types = 1);

namespace core\broadcast\task;

use core\Core;
use core\CorePlayer;

use core\broadcast\Broadcast;

use pocketmine\scheduler\Task;

class DurationSend extends Task {
    private $core;

    private $display = "", $display2 = "";

    private $player = null;

    private $duration = 0, $current = 0;

    private $type;
    
    public function __construct(Core $core, $type, $player = null, int $duration, string $display, string $display2 = "") {
        $this->core = $core;

        $this->type = $type;
        $this->player = $player;
        $this->duration = $duration;
        $this->current = 0;
        $this->display = $display;
        $this->display2 = $display2;
    }

    public function onRun(int $currentTick) {
        if($this->current <= $this->duration) {
            $this->core->getScheduler()->cancelTask($this->getTaskId());
        }
        switch($this->type) {
            case Broadcast::POPUP:
                if($this->player instanceof CorePlayer) {
                    $this->player->sendPopup(str_replace("{PLAYER}", $this->player->getName(), $this->display));
                } else {
                    foreach($this->core->getServer()->getOnlinePlayers() as $players) {
                        $players->sendPopup(str_replace("{PLAYER}", "*", $this->display));
                    }
                }
            break;
            case Broadcast::TITLE:
                if($this->player instanceof CorePlayer) {
                    $this->player->addTitle(str_replace("{PLAYER}", $this->player->getName(), $this->display));
                    $this->player->addSubTitle(str_replace("{PLAYER}", $this->player->getName(), $this->display2));
                } else {
                    foreach($this->core->getServer()->getOnlinePlayers() as $players) {
                        $players->addTitle(str_replace("{PLAYER}", "*", $this->display));
                        $players->addSubTitle(str_replace("{PLAYER}", "*", $this->display2));
                    }
                }
            break;
        }
        $this->current += 1;
    }
}

