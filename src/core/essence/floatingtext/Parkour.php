<?php

namespace core\essence\floatingtext;

use lobby\Lobby;

use pocketmine\level\Position;

class Parkour extends FloatingText {
    public function __construct() {
        parent::__construct("Parkour");
    }

    public function getPosition() : Position {
        return new Position(126, 15, 98, "Lobby");
    }

    public function getText() : string {
        return Lobby::getInstance()->getPrefix() . "Parkour coming Soon!";
    }
}