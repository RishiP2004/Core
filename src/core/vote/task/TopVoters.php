<?php

declare(strict_types = 1);

namespace core\vote\task;

use core\Core;
use core\CorePlayer;

use core\utils\Website;

use pocketmine\scheduler\AsyncTask;

use pocketmine\Server;

class TopVoters extends AsyncTask {
	private $apiKey = "";

	private $limit = 0;

	public function __construct(string $apiKey, int $limit) {
		$this->apiKey = $apiKey;
		$this->limit = $limit;
	}

	public function onRun() {
		$this->setResult(Website::getURL("https://minecraftpocket-servers.com/api/?object=servers&element=voters&key=" . $this->apiKey . "&month=current&format=json&limit=" . $this->limit));
	}

	public function onCompletion(Server $server) {
		$result = $this->getResult();

		if(explode(" ", $result)[0] === "Error:") {
			return;
		}
		$voters = json_decode($result, true)["voters"];

		foreach($voters as $index => $voteData) {
			if(!CorePlayer::isValidUsername($voters['nickname'])) {
				unset($voters[$index]);
			}
		}
		Core::getInstance()->getVote()->setTopVoters($voters);
	}
}