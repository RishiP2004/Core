<?php

declare(strict_types = 1);

namespace core\stats\rank;

use pocketmine\utils\TextFormat;

class Pixelated extends Rank {
    public function __construct() {
        parent::__construct("Pixelated");

        $this->setPaidPrice(15);
    }

	public function getFormat() : string {
		return TextFormat::BOLD . TextFormat::DARK_RED . "PIXELATED" . TextFormat::RESET;
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

    public function getInheritance() : Rank {
        return new Universal();
    }

    public function getValue() : int {
        return self::PAID;
    }

    public function getChatTime() : float {
    	return 0;
    }
}