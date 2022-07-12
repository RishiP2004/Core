<?php

declare(strict_types = 1);

namespace core\broadcast\task;

use core\Core;

use core\player\CorePlayer;

use core\broadcast\BroadcastManager;

use pocketmine\scheduler\Task;

class DurationSendTask extends Task {
	private int $current = 0;

    public function __construct(private string $type, private ?CorePlayer $player, private int $duration = 0, private string $display = "", private string $display2 = "") {}

    public function onRun() : void {
        if($this->current <= $this->duration) {
			$this->getHandler()->cancel();
        }
        switch($this->type) {
            case BroadcastManager::POPUP:
                if($this->player instanceof CorePlayer) {
                    $this->player->sendPopup(str_replace("{PLAYER}", $this->player->getName(), $this->display));
                } else {
                    foreach(Core::getInstance()->getServer()->getOnlinePlayers() as $players) {
                        $players->sendPopup(str_replace("{PLAYER}", "*", $this->display));
                    }
                }
            break;
            case BroadcastManager::TITLE:
                if($this->player instanceof CorePlayer) {
                    $this->player->sendTitle(str_replace("{PLAYER}", $this->player->getName(), $this->display));
                    $this->player->sendSubTitle(str_replace("{PLAYER}", $this->player->getName(), $this->display2));
                } else {
                    foreach(Core::getInstance()->getServer()->getOnlinePlayers() as $player) {
                        $player->sendTitle(str_replace("{PLAYER}", "*", $this->display));
                        $player->sendSubTitle(str_replace("{PLAYER}", "*", $this->display2));
                    }
                }
            break;
        }
        $this->current += 1;
    }
}

