<?php

declare(strict_types = 1);

namespace core\vote\task;

use core\player\CorePlayer;
use core\vote\VoteManager;

use pocketmine\scheduler\AsyncTask;

use pocketmine\utils\Internet;

class TopVotersTask extends AsyncTask {
	public function __construct(private string $apiKey, private int $limit) {}

	public function onRun() : void {
		$this->setResult(Internet::getURL("https://minecraftpocket-servers.com/api/?object=servers&element=voters&key=" . $this->apiKey . "&month=current&format=json&limit=" . $this->limit));
	}

	public function onCompletion() : void {
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
		VoteManager::getInstance()->setTopVoters($voters);
	}
}