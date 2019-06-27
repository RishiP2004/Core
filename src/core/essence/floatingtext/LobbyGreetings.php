<?php

declare(strict_types = 1);

namespace core\essence\floatingtext;

use lobby\Lobby;

use pocketmine\Server;

use pocketmine\level\Position;

use pocketmine\utils\TextFormat;

class LobbyGreetings extends FloatingText {
    public function __construct() {
        parent::__construct("LobbyGreetings");
    }

    public function getPosition() : Position {
		$level = Server::getInstance()->getLevelByName("Lobby");
		
        return new Position(126, 15, 98, $level);
    }

    public function getText() : string {
        return Lobby::getInstance()->getPrefix() . "Welcome to the Athena Lobby!\n" . TextFormat::GRAY . "There are currently {TOTAL_ONLINE_PLAYERS}/{TOTAL_MAX_SLOTS} Online!\n" . TextFormat::GRAY . "Pick a server, or just hang around in the Lobby";
    }
}