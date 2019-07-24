<?php

declare(strict_types = 1);

namespace core\world\area;

use pocketmine\level\Position;

abstract class Area {
    const FLY_VANILLA = 0;
    const FLY_ENABLE = 1;
    const FLY_DISABLE = 2;
    const FLY_SUPERVISED = 3;

    private $name = "";

    public function __construct(string $name) {
        $this->name = $name;
    }

    public final function getName() : string {
        return $this->name;
    }

    public abstract function getPosition1() : Position;

    public abstract function getPosition2() : Position;

    public abstract function allowedEnter() : bool;

    public abstract function allowedLeave() : bool;

    public abstract function getEnterNotifications() : string;

    public abstract function getLeaveNotifications() : string;

    public abstract function editable() : bool;

    public abstract function PvP() : bool;

    public abstract function entityDamage() : bool;

    public abstract function usable() : bool;

    public abstract function consume() : bool;

    public abstract function projectile() : bool;

    public abstract function itemDrop() : bool;

    public abstract function itemPickup() : bool;

    public abstract function inventoryTransaction() : bool;

    public abstract function exhaust() : bool;

    public abstract function explosion() : bool;

    public abstract function sleep() : bool;

    public abstract function sendChat() : bool;

    public abstract function receiveChat() : bool;

    public abstract function getFly() : int;

    public abstract function getGamemode() : int;

    public abstract function getAreaEffects() : array;

    public abstract function getBlockedCommands() : array;

    public abstract function entitySpawn() : bool;
}