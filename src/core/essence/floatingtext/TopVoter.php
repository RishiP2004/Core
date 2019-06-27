<?php

declare(strict_types = 1);

namespace core\essence\floatingtext;

use core\Core;

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
		
		return new Position(126, 15, 98, $level);
	}

	public function getText() : string {
		$voters = Core::getInstance()->getVote()->getTopVoters(10);
		$i = 1;

		$text = Lobby::getInstance()->getPrefix() . "Top Voters this Month:";

		foreach($voters as $vote) {
			$text .= TextFormat::GRAY . "#" . $i . ". " . $vote["nickname"] . ": " . $vote["votes"];
			$i++;
		}
		return $text;
	}
}