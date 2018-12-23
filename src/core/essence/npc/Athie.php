<?php

namespace core\essence\npc;

use core\utils\Entity;

use pocketmine\level\Position;

use pocketmine\entity\Skin;

use pocketmine\utils\TextFormat;

use pocketmine\item\Item;

class Athie extends NPC {
    public function __construct() {
        parent::__construct("LobbyGreetings");
    }

    public function getPosition() : Position {
        return new Position(138.5, 15, 127.5, "LobbyGreetings");
    }

    public function getSize() : float {
        return 1.75;
    }

    public function getNameTag() : string {
        return TextFormat::BOLD . TextFormat::DARK_GRAY . "[NPC]" . TextFormat::RESET . TextFormat::BLUE . " Athie";
    }

    public function getSkin() : Skin {
        $image = imagecreatefrompng("https://jpsierens.files.wordpress.com/2012/10/skin.png");

        return new Skin($this->getName(), Entity::skinFromImage($image) ?? "", "", "geometry.humanoid.custom", "");
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
            "103, 12, 85, LobbyGreetings",
            "104, 15, 86, LobbyGreetings",
            "104, 15, 87, LobbyGreetings"
        ];
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