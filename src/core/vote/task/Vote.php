<?php

declare(strict_types = 1);

namespace core\vote\task;

use core\Core;
use core\CorePlayer;

use core\utils\Website;

use pocketmine\scheduler\AsyncTask;

use pocketmine\Server;

class Vote extends AsyncTask {
	private $apiKey = "", $username = "";

	public function __construct(string $apiKey, string $username) {
		$this->apiKey = $apiKey;
		$this->username = $username;
	}

	public function onRun() : void {
		$result = Website::getURL("https://minecraftpocket-servers.com/api/?object=votes&element=claim&key=" . $this->apiKey . "&username=" . str_replace(" ", "+", $this->username));

		if($result === "1") {
			Website::getURL("https://minecraftpocket-servers.com/api/?action=post&object=votes&element=claim&key=" . $this->apiKey . "&username=" . str_replace(" ", "+", $this->username));
		}
		$this->setResult($result);
	}

	public function onCompletion(Server $server) : void {
		$result = $this->getResult();
		$player = $server->getPlayer($this->username);

		if($player instanceof CorePlayer) {
			if($player === null or Core::getInstance() === null) {
				return;
			}
			switch($result) {
				case "0":
					\core\vote\Vote::getInstance()->removeFromQueue($player->getCoreUser());
					$player->sendMessage(Core::ERROR_PREFIX . "You have not Voted yet");
				return;
				case "1":
					\core\vote\Vote::getInstance()->removeFromQueue($player->getCoreUser());
					$player->claimVote();
				return;
				case "2":
					\core\vote\Vote::getInstance()->removeFromQueue($player->getCoreUser());
					$player->sendMessage(Core::ERROR_PREFIX . "You have already Voted today");
				return;
				default:
					\core\vote\Vote::getInstance()->removeFromQueue($player->getCoreUser());
					$player->sendMessage(Core::ERROR_PREFIX . "An error occurred whilst trying to Vote");
				return;
			}
		}
	}
}