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

class Factions extends NPC {
    public function __construct() {
        parent::__construct("Factions");
    }

    public function getPosition() : Position {
		$level = Server::getInstance()->getLevelByName("Lobby");

        return new Position(138.5, 15, 127.5, $level);
    }

    public function getSize() : float {
        return 1.75;
    }

    public function getNameTag() : string {
        return TextFormat::BOLD . TextFormat::DARK_GRAY . "[NPC]" . TextFormat::RESET . TextFormat::RED . " Factions\n" . TextFormat::GRAY . "Online: {ONLINE}\n" . TextFormat::GRAY . "{ONLINE_PLAYERS}{MAX_SLOTS}";
    }

    public function getSkin() : Skin {
		return new Skin($this->getName(), Entity::skinFromImage(Core::getInstance()->getDataFolder() . "/stats/factions.png") ?? Core::getInstance()->getDataFolder() . "/stats/fallback.png");
    }

    public function getHeldItem() : Item {
        return Item::get(276);
    }

    public function getArmor() : array {
        return [
            "helmet" => 310,
            "chestplate" => 311,
            "leggings" => 312,
            "boots" => 313
        ];
    }

    public function rotate() : bool {
        return true;
    }

    public function getMovement() : array {
        return [];
    }
	
	public function getMoveTime() : int {
		return 1;
	}

    public function getCommands() : array {
        return [
            "transfer play.athena.me 19141 {PLAYER}"
        ];
    }

    public function getMessages() : array {
        return [
            TextFormat::BOLD . TextFormat::DARK_GRAY . "[NPC]" . TextFormat::RESET . TextFormat::RED . " Factions" . TextFormat::DARK_GREEN . "> " . TextFormat::GRAY . "Hi {PLAYER}, Factions server is coming soon!",
            TextFormat::GRAY . "If you want to help test, contact us on TwitterSend (@AthenaBE) or Discord () \n"
        ];
    }
}