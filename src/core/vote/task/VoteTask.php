<?php

declare(strict_types = 1);

namespace core\vote\task;

use core\Core;

use core\player\CorePlayer;

use core\vote\VoteManager;

use pocketmine\utils\Internet;

use pocketmine\scheduler\AsyncTask;

use pocketmine\Server;

class VoteTask extends AsyncTask {
	public function __construct(private string $apiKey, private string $username) {}

	public function onRun() : void {
		$result = Internet::getURL("https://minecraftpocket-servers.com/api/?object=votes&element=claim&key=" . $this->apiKey . "&username=" . str_replace(" ", "+", $this->username));

		if($result == "1") {
			Internet::getURL("https://minecraftpocket-servers.com/api/?action=post&object=votes&element=claim&key=" . $this->apiKey . "&username=" . str_replace(" ", "+", $this->username));
		}
		$this->setResult($result);
	}

	public function onCompletion() : void {
		$result = $this->getResult();
		$player = Server::getInstance()->getPlayerByPrefix($this->username);

		if($player instanceof CorePlayer) {
			VoteManager::getInstance()->removeFromQueue($player->getCoreUser());

			switch($result) {
				case "0":
					$player->sendMessage(Core::ERROR_PREFIX . "You have not Voted yet");
				return;
				case "1":
					$player->claimVote();
				return;
				case "2":
					$player->sendMessage(Core::ERROR_PREFIX . "You have already Voted today");
				return;
				default:
					$player->sendMessage(Core::ERROR_PREFIX . "An error occurred whilst trying to VoteManager");
				return;
			}
		}
	}
}