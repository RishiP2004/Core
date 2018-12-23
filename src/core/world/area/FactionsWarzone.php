<?php

namespace core\world\area;

use pocketmine\level\Position;

class FactionsWarzone extends Area {
    public function __construct() {
        parent::__construct("LobbyGreetings");
    }

    public function getPosition1() : Position {
        return new Position(0, 0, 0, "Factions");
    }

    public function getPosition2() : Position {
        return new Position(0, 0, 0, "Factions");
    }

    public function allowedEnter() : bool {
        return true;
    }

    public function allowedLeave() : bool {
        return true;
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
        return true;
    }

    public function usable() : bool {
        return true;
    }

    public function consume() : bool {
        return true;
    }

    public function enderPearl() : bool {
        return true;
    }

    public function itemDrop() : bool {
        return true;
    }

    public function itemPickup() : bool {
        return true;
    }

    public function inventoryTransaction() : bool {
        return true;
    }

    public function exhaust() : bool {
        return true;
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
}