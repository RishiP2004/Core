<?php

declare(strict_types = 1);

namespace core\vote;

use core\CoreUser;

use core\utils\Manager;

use core\vote\task\TopVoters;

class Vote extends Manager implements VoteData {
    public static $instance = null;
    
    public $queue = [], $lists = [], $topVoters = [];

    private $runs = 0;
    
    public function init() {
        self::$instance = $this;

        if(!empty(self::API_KEY)) {
			$this->registerCommand(\vote\command\Vote::class, new \vote\command\Vote($this));
		}
    }

    public static function getInstance() : self {
		return self::$instance;
	}

	public function tick() : void {
    	$this->runs++;

    	if(!empty(self::API_KEY)) {
    		if($this->runs % self::VOTE_UPDATE === 0) {
				$this->registerAsyncTank(new TopVoters(self::API_KEY, self::TOP_VOTERS_LIMIT));
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