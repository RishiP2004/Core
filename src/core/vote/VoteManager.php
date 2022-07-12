<?php

declare(strict_types = 1);

namespace core\vote;

use core\Core;

use core\player\CoreUser;

use core\utils\Manager;

use core\vote\command\VoteCommand;

use core\vote\task\TopVotersTask;
//Maybe better from DN?
class VoteManager extends Manager implements VoteData {
    public static $instance = null;
    
    public $queue = [], $lists = [], $topVoters = [];

    private $runs = 0;
    
    public function init() : void {
        self::$instance = $this;

        if(!empty(self::API_KEY)) {
			$this->registerPermissions([
				"vote.command" => [
					"default" => "op",
					"description" => "Vote command"
				],
			]);
			$this->registerCommands(VoteCommand::class, new VoteCommand(Core::getInstance(), "vote", "Vote Command"));
		}
    }

    public static function getInstance() : self {
		return self::$instance;
	}

	public function tick() : void {
    	$this->runs++;

    	if(!empty(self::API_KEY)) {
    		if($this->runs % self::VOTE_UPDATE === 0) {
				$this->registerAsyncTank(new TopVotersTask(self::API_KEY, self::TOP_VOTERS_LIMIT));
			}
		}
	}

	public function getQueue() : array {
		return $this->queue;
	}

	public function addToQueue(CoreUser $user) : void {
		$this->queue = array_merge($this->queue, $user->getName());
	}

	public function removeFromQueue(CoreUser $user) : void {
		unset($this->queue[$user->getName()]);
	}

	public function getTopVoters() : array {
		return $this->topVoters;
	}

	public function setTopVoters(array $topVoters) {
    	$this->topVoters = $topVoters;
	}
}