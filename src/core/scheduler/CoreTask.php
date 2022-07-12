<?php

declare(strict_types = 1);

namespace core\scheduler;

use core\Core;
use core\anticheat\AntiCheatManager;
use core\broadcast\BroadcastManager;
use core\essence\EssenceManager;
use core\essential\EssentialManager;
use core\network\NetworkManager;
use core\vote\VoteManager;

use pocketmine\scheduler\Task;

class CoreTask extends Task {
    private int $runs = 0;

    public function __construct(private Core $core) {}

    public function onRun() : void {
        $this->runs++;

        if($this->runs % 20 === 0) {
        	AntiCheatManager::getInstance()->tick();
        	BroadcastManager::getInstance()->tick();
        	EssenceManager::getInstance()->tick();
        	EssentialManager::getInstance()->tick();
        	VoteManager::getInstance()->tick();
        }
		if($this->runs * 20 * 60) {
			NetworkManager::getInstance()->tick();
		}
    }
}