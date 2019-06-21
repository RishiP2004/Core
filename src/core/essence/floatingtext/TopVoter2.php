<?php

declare(strict_types = 1);

namespace core\essence\floatingtext;

use core\Core;

use factions\Factions;

use pocketmine\level\Position;

use pocketmine\utils\TextFormat;

class TopVoter2 extends FloatingText {
    public function __construct() {
        parent::__construct("TopVoter2");
    }

    public function getPosition() : Position {
        return new Position(126, 15, 98, "Factions");
    }

    public function getText() : string {
		$voters = Core::getInstance()->getVote()->getTopVoters(10);
		$i = 1;

		$text = Factions::getInstance()->getPrefix() . "Top Voters this Month:";

		foreach($voters as $vote) {
			$text .= TextFormat::GRAY . "#" . $i . ". " . $vote["nickname"] . ": " . $vote["votes"];
			$i++;
		}
		return $text;
    }
}