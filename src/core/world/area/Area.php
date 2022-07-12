<?php

declare(strict_types = 1);

namespace core\world\area;

use pocketmine\world\Position;

use pocketmine\player\GameMode;

class Area {
    const FLY_VANILLA = 0;
    const FLY_ENABLE = 1;
    const FLY_DISABLE = 2;
    const FLY_SUPERVISED = 3;

    public function __construct(
		protected string $name,
		private Position $position1,
		private Position $position2,
		private bool $damage = false,
		private bool $editable = false,
		private bool $explosion = false,
		private bool $allowedEnter = true,
		private bool $allowedLeave = true,
		private string $enterNotifications = "",
		private string $leaveNotifications = "",
		private bool $entityDamage = false,
		private bool $usage = false,
		private bool $consume = false,
		private bool $projectile = false,
		private bool $itemDrop = false,
		private bool $itemPickup = false,
		private bool $inventoryTransaction = true,
		private bool $exhaust = false,
		private bool $sleep = true,
		private bool $sendChat = true,
		private bool $receiveChat = true,
		private int $getFly = self::FLY_DISABLE,
		private int $gamemode = 0,
		private array $areaEffects = [],
		private array $blockedCommands = [],
		private bool $entitySpawn = true
	) {}

    public final function getName() : string {
        return $this->name;
    }

    public function getPosition1() : Position {
    	return $this->position1;
	}

    public function getPosition2() : Position {
    	return $this->position2;
	}

    public function allowedEnter() : bool {
    	return $this->allowedEnter;
	}

    public function allowedLeave() : bool {
    	return $this->allowedLeave;
	}

    public function getEnterNotifications() : string {
    	return $this->enterNotifications;
	}

    public function getLeaveNotifications() : string {
    	return $this->leaveNotifications;
	}

    public function editable() : bool {
    	return $this->editable;
	}

    public function damage() : bool {
    	return $this->damage;
	}

    public function entityDamage() : bool {
    	return $this->entityDamage;
	}

    public function usable() : bool {
    	return $this->usage;
	}

    public function consume() : bool {
    	return $this->consume;
	}

    public function projectile() : bool {
    	return $this->projectile;
	}

    public function itemDrop() : bool {
    	return $this->itemDrop;
	}

    public function itemPickup() : bool {
    	return $this->itemPickup;
	}

    public function inventoryTransaction() : bool {
    	return $this->inventoryTransaction;
	}

    public function exhaust() : bool {
    	return $this->exhaust;
	}

    public function explosion() : bool {
    	return $this->explosion;
	}

    public function sleep() : bool {
    	return $this->sleep;
	}

    public function sendChat() : bool {
    	return $this->sendChat;
	}

    public function receiveChat() : bool {
    	return $this->receiveChat;
	}

    public function getFly() : int {
    	return $this->getFly;
	}

    public function getGamemode() : GameMode {
    	return GameMode::fromString((string) $this->gamemode);
	}

    public function getAreaEffects() : array {
		return $this->areaEffects;
	}

    public function getBlockedCommands() : array {
		return $this->blockedCommands;
	}

    public function entitySpawn() : bool {
		return $this->entitySpawn;
	}
}