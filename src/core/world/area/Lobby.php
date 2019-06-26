<?php

declare(strict_types = 1);

namespace core\world\area;

use pocketmine\Server;

use pocketmine\level\Position;

class Lobby extends Area {
    public function __construct() {
        parent::__construct("Lobby");
    }

    public function getPosition1() : Position {
		$level = Server::getInstance()->getLevelByName("Lobby");

        return new Position(0, 0, 0, $level);
    }

    public function getPosition2() : Position {
		$level = Server::getInstance()->getLevelByName("Lobby");

        return new Position(0, 0, 0, $level);
    }

    public function allowedEnter() : bool {
        return true;
    }

    public function allowedLeave() : bool {
        return false;
    }

    public function getEnterNotifications() : string {
        return "";
    }

    public function getLeaveNotifications() : string {
        return "";
    }

    public function editable() : bool {
        return false;
    }

    public function PvP() : bool {
        return false;
    }

    public function usable() : bool {
        return false;
    }

    public function consume() : bool {
        return false;
    }

    public function enderPearl() : bool {
        return false;
    }

    public function itemDrop() : bool {
        return false;
    }

    public function itemPickup() : bool {
        return false;
    }

    public function inventoryTransaction() : bool {
        return false;
    }

    public function exhaust() : bool {
        return false;
    }

    public function explosion() : bool {
        return false;
    }

    public function sleep() : bool {
        return false;
    }

    public function sendChat() : bool {
        return true;
    }

    public function receiveChat() : bool {
        return true;
    }

    public function getFly() : int {
        return self::FLY_DISABLE;
    }

    public function getGamemode() : int {
        return 0;
    }

    public function getAreaEffects() : array {
        return [];
    }

    public function getBlockedCommands() : array {
        return [];
    }

    public function entitySpawn() : bool {
		return false;
	}
}