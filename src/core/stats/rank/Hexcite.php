<?php

declare(strict_types = 1);

namespace core\stats\rank;

use pocketmine\utils\TextFormat;

class Hexcite extends Rank {
    public function __construct() {
        parent::__construct("Hexcite");

        $this->setFreePrice(5000);
    }

	public function getFormat() : string {
		return TextFormat::BOLD . TextFormat::BLUE . "HEXCITE" . TextFormat::RESET;
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
        return self::FREE;
    }

    public function getChatTime() : float {
    	return 2.5;
    }
}