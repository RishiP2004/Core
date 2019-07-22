<?php

declare(strict_types = 1);

namespace core\essence\floatingtext;

use core\Core;
use core\vote\VoteData;
use lobby\Lobby;

use pocketmine\Server;

use pocketmine\level\Position;

use pocketmine\utils\TextFormat;

class TopVoter extends FloatingText {
	public function __construct() {
		parent::__construct("TopVoter");
	}

	public function getPosition() : Position {
		$level = Server::getInstance()->getLevelByName("Lobby");
		
		return new Position(126, 13, 104, $level);
	}

	public function getText() : string {
		if(!empty(VoteData::API_KEY)) {
			return "";
		}
		$voters = Core::getInstance()->getVote()->getTopVoters();
		$i = 1;

		$text = Lobby::getInstance()->getPrefix() . "Top Voters this Month:";

		foreach($voters as $vote) {
			$text .= TextFormat::GRAY . "#" . $i . ". " . $vote["nickname"] . ": " . $vote["votes"];
			$i++;
		}
		return $text;
	}

	public function getUpdateTime() : ?int {
		return VoteData::VOTE_UPDATE;
	}
}