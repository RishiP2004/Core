<?php

declare(strict_types = 1);

namespace core\vote;

use core\Core;
use core\CoreUser;

use core\vote\task\TopVoters;

class Vote implements VoteData {
    private $core;
    
    public $queue = [], $lists = [], $topVoters = [];

    private $runs = 0;
    
    public function __construct(Core $core) {
        $this->core = $core;

        if(!empty(self::API_KEY)) {
			$core->getServer()->getCommandMap()->register("vote", new \vote\command\Vote($core));
		}
    }

    public function tick() : void {
    	$this->runs++;

    	if(!empty(self::API_KEY)) {
    		if($this->runs % self::VOTE_UPDATE === 0) {
				$this->core->getServer()->getAsyncPool()->submitTask(new TopVoters(self::API_KEY, self::TOP_VOTERS_LIMIT));
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