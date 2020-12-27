<?php

declare(strict_types = 1);

namespace core\essence\npc;

use core\Core;
use core\network\Network;
use core\utils\Entity;

use pocketmine\Server;

use pocketmine\level\Position;

use pocketmine\entity\Skin;

use pocketmine\utils\TextFormat;

use pocketmine\item\Item;

class Lobby extends NPC {
    public function __construct() {
        parent::__construct("Lobby");
    }

    public function getPosition() : Position {
		$level = Server::getInstance()->getLevelByName("Lobby");

        return new Position(132.5, 15, 128.5, $level);
    }

    public function getSize() : float {
        return 1.75;
    }

    public function getNameTag() : string {
        return TextFormat::BOLD . TextFormat::DARK_GRAY . "[NPC]" . TextFormat::RESET . TextFormat::GREEN . " Lobby\n" . TextFormat::GRAY . "Online: {ONLINE}\n" . TextFormat::GRAY . "{ONLINE_PLAYERS}{MAX_SLOTS}";
    }

    public function getSkin() : Skin {
        return Entity::skinFromImage($this->getName(), (Core::getInstance()->getDataFolder() . "/stats/lobby.png") ?? Core::getInstance()->getDataFolder() . "/stats/fallback.png");
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
        return true;
    }

    public function getMovement() : array {
        return [];
    }

	public function getMoveTime() : int {
		return 1;
	}

    public function getCommands() : array {
    	$ip = Network::getInstance()->getServer(\core\network\server\Lobby::class)->getIp();
		$port = Network::getInstance()->getServer(\core\network\server\Lobby::class)->getPort();

        return [
            "transfer " . $ip . " " . $port . " {PLAYER}"
        ];
    }

    public function getMessages() : array {
        return [
            TextFormat::BOLD . TextFormat::DARK_GRAY . "[NPC]" . TextFormat::RESET . TextFormat::GREEN . " Lobby" . TextFormat::DARK_GREEN . "> " . TextFormat::GRAY . "Hi {PLAYER}, reconnecting you to the Lobby!"
        ];
    }
}