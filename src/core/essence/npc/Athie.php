<?php

declare(strict_types = 1);

namespace core\essence\npc;

use core\Core;

use core\utils\Entity;

use pocketmine\Server;

use pocketmine\level\Position;

use pocketmine\entity\Skin;

use pocketmine\utils\TextFormat;

use pocketmine\item\Item;

class Athie extends NPC {
    public function __construct() {
        parent::__construct("LobbyGreetings");
    }

    public function getPosition() : Position {
    	$level = Server::getInstance()->getLevelByName("Lobby");

        return new Position(126, 14, 115, $level);
    }

    public function getSize() : float {
        return 1.75;
    }

    public function getNameTag() : string {
        return TextFormat::BOLD . TextFormat::DARK_GRAY . "[NPC]" . TextFormat::RESET . TextFormat::BLUE . " Athie";
    }

    public function getSkin() : Skin {
		return new Skin($this->getName(), Entity::skinFromImage(Core::getInstance()->getDataFolder() . "/stats/athie.png") ?? Core::getInstance()->getDataFolder() . "/stats/fallback.png");
    }

    public function getHeldItem() : Item {
        return Item::get(0);
    }

    public function getArmor() : array {
        return [
            "helmet" => "",
            "chestplate" => "",
            "leggings" => "",
            "boots" => ""
        ];
    }

    public function rotate() : bool {
        return false;
    }

    public function getMovement() : array {
        return [
            1 => "103, 12, 85, Lobby",
            2 => "104, 15, 86, Lobby",
            3 => "104, 15, 87, Lobby"
        ];
    }
	
	public function getMoveTime() : int {
		return 3;
	}

    public function getCommands() : array {
        return [];
    }

    public function getMessages() : array {
        return [
            TextFormat::BOLD . TextFormat::DARK_GRAY . "[NPC]" . TextFormat::RESET . TextFormat::BLUE . " Athie" . TextFormat::DARK_GREEN . "> " . TextFormat::GRAY . "Hi {PLAYER}, I'm better than Derpific!"
        ];
    }
}