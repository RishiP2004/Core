<?php

declare(strict_types = 1);

namespace core\essence\floatingtext;

use lobby\Lobby;

use pocketmine\Server;

use pocketmine\level\Position;

class Parkour extends FloatingText {
    public function __construct() {
        parent::__construct("Parkour");
    }

    public function getPosition() : Position {
		$level = Server::getInstance()->getLevelByName("Lobby");
		
        return new Position(126, 15, 98, $level);
    }

    public function getText() : string {
        return Lobby::getInstance()->getPrefix() . "Parkour coming Soon!";
    }
}