<?php

declare(strict_types = 1);

namespace core\stats\rank;

use pocketmine\utils\TextFormat;

class Player extends Rank {
    public function __construct() {
        parent::__construct("Player");
    }

	public function getFormat() : string {
		return TextFormat::YELLOW;
	}

	public function getChatFormat() : string {
		return $this->getFormat() . "{DISPLAY_NAME}" . TextFormat::DARK_GRAY . ": " . TextFormat::GRAY . "{MESSAGE}";
	}

	public function getNameTagFormat() : string {
		return $this->getFormat() . "{DISPLAY_NAME}";
	}

    public function getPermissions() : array {
    	return [];
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