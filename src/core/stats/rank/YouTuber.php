<?php

declare(strict_types = 1);

namespace core\stats\rank;

use pocketmine\utils\TextFormat;

class YouTuber extends Rank {
    public function __construct() {
        parent::__construct("YouTuber");
    }

	public function getFormat() : string {
		return TextFormat::BOLD . TextFormat::BLACK . "You" . TextFormat::DARK_RED . "Tube" . TextFormat::RESET;
	}

	public function getChatFormat() : string {
		return $this->getFormat() . "{DISPLAY_NAME}" . TextFormat::LIGHT_PURPLE . ": " . TextFormat::GRAY . "{MESSAGE}";
	}

	public function getNameTagFormat() : string {
		return $this->getFormat() . "{DISPLAY_NAME}";
	}

    public function getPermissions() : array {
        return [];
    }

    public function getInheritance() : ?Rank {
        return new Athener();
    }

    public function getValue() : int {
        return self::STAFF;
    }

    public function getChatTime() : float {
        return 0;
    }
}