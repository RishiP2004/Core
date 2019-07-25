<?php

declare(strict_types = 1);

namespace core\stats\rank;

use pocketmine\utils\TextFormat;

class Player extends Rank {
    public function __construct() {
        parent::__construct("Player");
    }

	public function getFormat() : string {
		return TextFormat::YELLOW . "[PLAYER] " . TextFormat::RESET;
	}

	public function getChatFormat() : string {
		return $this->getFormat() . "{DISPLAY_NAME}" . TextFormat::DARK_GRAY . ": " . TextFormat::GRAY . "{MESSAGE}";
	}

	public function getNameTagFormat() : string {
		return $this->getFormat() . "{DISPLAY_NAME}";
	}

    public function getPermissions() : array {
    	return [
			"core.essentials.command.hud",
			"core.essentials.command.ping",
			"core.essentials.defaults.command.list"
		];
    }

    public function getInheritance() : ?Rank {
        return null;
    }

    public function getValue() : int {
        return self::DEFAULT;
    }

    public function getChatTime() : float {
    	return 3;
    }
}