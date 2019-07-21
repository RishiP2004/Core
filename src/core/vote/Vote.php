<?php

declare(strict_types = 1);

namespace core\vote;

use core\Core;
use core\CorePlayer;
use core\CoreUser;

use core\vote\task\TopVoters;

class Vote implements VoteData {
    private $core;
    
    public $queue = [], $lists = [], $topVoters = [];

    private $runs = 0;
    
    public function __construct(Core $core) {
        $this->core = $core;

        if(!empty($this->getAPIKey())) {
			$core->getServer()->getCommandMap()->register("vote", new \vote\command\Vote($core));
		}
    }

    public function getAPIKey() : string {
        return self::API_KEY;
    }

    public function getVoteUpdate() : int {
    	return self::VOTE_UPDATE;
	}

    public function getItems() : array {
        return self::ITEMS;
    }
    
    public function getCommands() : array {
        return self::COMMANDS;
    }

    public function getTopVotersLimit() : int {
    	return self::TOP_VOTERS_LIMIT;
	}

    public function tick() {
    	$this->runs++;

    	if(!empty($this->getAPIKey())) {
    		if($this->runs % $this->getVoteUpdate() === 0) {
				$this->core->getServer()->getAsyncPool()->submitTask(new TopVoters($this->getAPIKey(), $this->getTopVotersLimit()));
			}
		}
	}

	public function addToQueue(CoreUser $user) {
		$this->queue = array_merge($this->queue, $user->getName());
	}

	public function removeFromQueue(CoreUser $user) {
		unset($this->queue[$user->getName()]);
	}

	public function getTopVoters() : array {
		return $this->topVoters;
	}

	public function setTopVoters(array $topVoters) {
    	$this->topVoters = $topVoters;
	}
}