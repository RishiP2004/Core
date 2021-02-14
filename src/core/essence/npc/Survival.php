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

class Survival extends NPC {
    public function __construct() {
        parent::__construct("Survival");
    }

    public function getPosition() : Position {
		$level = Server::getInstance()->getLevelByName("Lobby");

        return new Position(138.5, 15, 127.5, $level);
    }

    public function getSize() : float {
        return 1.75;
    }

    public function getNameTag() : string {
        return TextFormat::BOLD . TextFormat::DARK_GRAY . "[NPC]" . TextFormat::RESET . TextFormat::RED . " Survival\n" . TextFormat::GRAY . "Online: {ONLINE}\n" . TextFormat::GRAY . "{ONLINE_PLAYERS}{MAX_SLOTS}";
    }

    public function getSkin() : Skin {
		return Entity::skinFromImage($this->getName(), (Core::getInstance()->getDataFolder() . "/stats/survival.png") ?? Core::getInstance()->getDataFolder() . "/stats/fallback.png");
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
		$ip = Network::getInstance()->getServer(\core\network\server\Survival::class)->getIp();
		$port = Network::getInstance()->getServer(\core\network\server\Survival::class)->getPort();

		return [
			"transfer " . $ip . " " . $port . " {PLAYER}"
		];
    }

    public function getMessages() : array {
        return [
            TextFormat::BOLD . TextFormat::DARK_GRAY . "[NPC]" . TextFormat::RESET . TextFormat::RED . " Survival" . TextFormat::DARK_GREEN . "> " . TextFormat::GRAY . "Hi {PLAYER}, Survival server is coming soon!",
            TextFormat::GRAY . "If you want to help test, contact us on Twitter! Send (@GratonePix) or our Discord a message!\n"
        ];
    }
}