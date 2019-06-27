<?php

declare(strict_types = 1);

namespace core\stats\rank;

use pocketmine\utils\TextFormat;

class Staff extends Rank {
    public function __construct() {
        parent::__construct("Staff");
    }

	public function getFormat() : string {
		return TextFormat::BOLD . TextFormat::DARK_PURPLE . "STAFF" . TextFormat::RESET;
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
        return new YouTuber();
    }

    public function getValue() : int {
    	return self::STAFF;
    }

    public function getChatTime() : float {
    	return 0;
    }
}