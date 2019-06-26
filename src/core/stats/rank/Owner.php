<?php

declare(strict_types = 1);

namespace core\stats\rank;

use pocketmine\utils\TextFormat;

class Owner extends Rank {
    public function __construct() {
        parent::__construct("Owner");
    }

	public function getFormat() : string {
		return TextFormat::BOLD . TextFormat::DARK_AQUA . "{OWNER}" . TextFormat::RESET;
	}

	public function getChatFormat() : string {
		return $this->getFormat() . "{DISPLAY_NAME}" . TextFormat::GREEN . ": " . TextFormat::GRAY . "{MESSAGE}";
	}

	public function getNameTagFormat() : string {
		return $this->getFormat() . "{DISPLAY_NAME}";
	}

	public function getPermissions() : array {
		return [];
	}

    public function getInheritance() : Rank {
        return new Manager();
    }

    public function getValue() : int {
        return self::STAFF;
    }

    public function getChatTime() : float {
    	return 0;
    }
}