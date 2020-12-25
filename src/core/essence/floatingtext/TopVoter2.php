<?php

declare(strict_types = 1);

namespace core\essence\floatingtext;

use core\Core;

use core\vote\{
	Vote,
	VoteData
};

use factions\Factions;

use pocketmine\Server;

use pocketmine\level\Position;

use pocketmine\utils\TextFormat;

class TopVoter2 extends FloatingText {
    public function __construct() {
        parent::__construct("TopVoter2");
    }

    public function getPosition() : Position {
		$level = Server::getInstance()->getLevelByName("Factions");
	
        return new Position(126, 15, 98, $level);
    }

    public function getText() : string {
		if(!empty(VoteData::API_KEY)) {
			return "";
		}
		$voters = Vote::getInstance()->getTopVoters();
		$i = 1;

		$text = Factions::PREFIX . "Top Voters this Month:";

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