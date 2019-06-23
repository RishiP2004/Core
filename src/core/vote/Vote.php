<?php

declare(strict_types = 1);

namespace core\vote;

use core\Core;
use core\CoreUser;

use core\vote\task\TopVotersTask;

class Vote implements VoteData {
    private $core;
    
    public $queue = [], $lists = [];
    
    public function __construct(Core $core) {
        $this->core = $core;

        if(!empty($this->getAPIKey())) {
			$core->getServer()->getCommandMap()->register("vote", new \vote\command\Vote($core));
		}
    }

    public function getAPIKey() : string {
        return self::API_KEY;
    }

    public function getItems() : array {
        return self::ITEMS;
    }
    
    public function getCommands() : array {
        return self::COMMANDS;
    }
	
	public function addToQueue(CoreUser $user) {
		$this->queue = array_merge($this->queue, $user->getName());
	}

	public function removeFromQueue(CoreUser $user) {
		unset($this->queue[$user->getName()]);
	}

	public function getTopVoters(int $display = 5) {
		$this->core->getServer()->getAsyncPool()->submitTask(new TopVotersTask($this->getAPIKey(), $display));
	}
}